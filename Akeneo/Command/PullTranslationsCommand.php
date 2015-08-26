<?php

namespace Akeneo\Command;

use Akeneo\Archive\Extractor;
use Akeneo\Archive\PackagesExtractor;
use Akeneo\Crowdin\Downloader;
use Akeneo\Crowdin\PackagesDownloader;
use Akeneo\Git\ProjectCloner;
use Akeneo\Git\PullRequestCreator;
use Akeneo\System\Executor;
use Akeneo\System\TranslationFilesCleaner;
use Crowdin\Client as CrowdinClient;
use Github\Client as GithubClient;
use Github\Exception\ValidationFailedException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Clone the project in a temporary directory, download translations from Crowdin, clean the translation files, open
 * a pull request on Github
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PullTranslationsCommand extends Command
{
    /** @var string */
    const LOG_FILE = 'pull-translations.log';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:pull-translations')
            ->setDescription('Fetch new translations from Crowdin and create pull requests to the Github repository')
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
            try {
                $pullRequestCreator->create($baseBranch, $downloadBaseDir, $projectDir, $edition, $username);
            } catch (ValidationFailedException $exception) {
                $message = sprintf(
                    'No PR created for version "%s", message "%s"',
                    $baseBranch,
                    $exception->getMessage()
                );
                $output->writeln(sprintf('<warning>%s<warning>', $message));
                $logger->addWarning($message);
            }
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

        $optionResolver = new OptionsResolver();
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
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        $logPath = sprintf('./app/%s', self::LOG_FILE);
        if (!is_file($logPath)) {
            $resource = fopen($logPath, 'w');
            fwrite($resource, '');
            fclose($resource);
        }
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
        $optionResolver->setRequired(['project', 'key', 'download', 'upload']);
        $crowdinOptions = $optionResolver->resolve($config['crowdin']);
        $crowdinProject = $crowdinOptions['project'];
        $crowdinKey = $crowdinOptions['key'];
        $crowdinClient = new CrowdinClient($crowdinProject, $crowdinKey);

        return $crowdinClient;
    }
}
