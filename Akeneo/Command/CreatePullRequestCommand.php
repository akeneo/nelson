<?php

namespace Akeneo\Command;

use Akeneo\Archive\Extractor;
use Akeneo\Archive\PackagesExtractor;
use Akeneo\Archive\TranslationFilesCleaner;
use Akeneo\Crowdin\Downloader;
use Akeneo\Crowdin\PackagesDownloader;
use Akeneo\Git\ProjectCloner;
use Akeneo\Git\PullRequestCreator;
use Akeneo\System\Executor;
use Crowdin\Client as CrowdinClient;
use Github\Client as GithubClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;


/**
 * Prepare the project in tmp, download translations from crowdin, and open a pull request
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class CreatePullRequestCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:create-pull-request')
            ->setDescription('Fetch new translations and create a pull request on the PIM repository')
            ->addArgument('username', InputArgument::REQUIRED, 'Github username')
            ->addArgument('edition', InputArgument::REQUIRED, 'PIM edition, community or enterprise');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->loadConfig();
        $username = $input->getArgument('username');
        $edition = $input->getArgument('edition');

        $logger = $this->getLogger($config);
        $githubClient = $this->getGithubClient($config);
        $crowdinClient = $this->getCrowdinClient($config);

        $executor = new Executor($logger);
        $cloner   = new ProjectCloner($executor);
        $downloader = new PackagesDownloader($crowdinClient);
        $extractor = new PackagesExtractor();
        $translationsCleaner = new TranslationFilesCleaner();
        $pullRequestCreator = new PullRequestCreator($executor, $githubClient);

        $downloadOptions = $this->resolveCrowdinDownloadOptions($config['crowdin']['download']);
        $branches = $downloadOptions[$edition]['branches'];
        $downloadBaseDir = $downloadOptions['base_dir'];
        $updateDir = $downloadBaseDir . '/update';
        $packages = $downloadOptions['packages'];
        $localesMap = $downloadOptions['locale_map'];

        foreach ($branches as $baseBranch) {
            $projectDir = $cloner->cloneProject($username, $updateDir, $edition, $baseBranch);
            $downloader->download($packages, $downloadBaseDir);
            $extractor->extract($packages, $downloadBaseDir, $updateDir);
            $translationsCleaner->cleanFiles($localesMap, $projectDir);
            $pullRequestCreator->create($baseBranch, $downloadBaseDir, $projectDir, $edition, $username);
        }

        $cmd = sprintf('rm -rf %s', $updateDir);
        $executor->execute($cmd);
    }

    /**
     * @return array
     */
    protected function loadConfig()
    {
        $configPath = realpath('./app/config.yml');
        $config = Yaml::parse(file_get_contents($configPath));

        $optionResolver =new OptionsResolver();
        $optionResolver->setRequired(['github', 'crowdin']);
        $config = $optionResolver->resolve($config);

        return $config;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function resolveCrowdinDownloadOptions(array $config)
    {
        $optionResolver = new OptionsResolver();
        $optionResolver->setRequired(['base_dir', 'packages', 'locale_map', 'community', 'enterprise']);
        $resolvedOptions = $optionResolver->resolve($config);

        return $resolvedOptions;
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        $logPath = realpath('./app/crowdin.log');
        $logger  = new Logger('crowdin');
        $logger->pushHandler(new StreamHandler($logPath, Logger::INFO));

        return $logger;
    }

    /**
     * @param array $config
     *
     * @return GithubClient
     */
    protected function getGithubClient(array $config)
    {
        $optionResolver = new OptionsResolver();
        $optionResolver->setRequired(['token']);
        $githubOptions = $optionResolver->resolve($config['github']);
        $githubToken = $githubOptions['token'];
        $githubClient = new GithubClient();
        $githubClient->authenticate($githubToken, null, GithubClient::AUTH_HTTP_TOKEN);

        return $githubClient;
    }

    /**
     * @param array $config
     *
     * @return CrowdinClient
     */
    protected function getCrowdinClient(array $config)
    {
        $optionResolver = new OptionsResolver();
        $optionResolver->setRequired(['project', 'key', 'download', 'update-file']);
        $crowdinOptions = $optionResolver->resolve($config['crowdin']);
        $crowdinProject = $crowdinOptions['project'];
        $crowdinKey = $crowdinOptions['key'];
        $crowdinClient = new CrowdinClient($crowdinProject, $crowdinKey);

        return $crowdinClient;
    }
}