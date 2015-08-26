<?php

namespace Akeneo\Command;

use Akeneo\Archive\Extractor;
use Akeneo\Archive\TranslationFilesCleaner;
use Akeneo\Crowdin\Downloader;
use Akeneo\Crowdin\TranslationFilesUpdater;
use Akeneo\Git\ProjectCloner;
use Akeneo\System\Executor;
use Akeneo\System\TranslationFilesProvider;
use Crowdin\Client as CrowdinClient;
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
 * Clone the project in a temporary directory, push any translation file (only english) to Crowdin
 *
 * Note that this command will not create new files on Crowdin
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PushTranslationKeysCommand extends Command
{
    /** @var string */
    const LOG_FILE = 'push-translation-keys.log';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:push-translation-keys')
            ->setDescription('Fetch new translation keys from Github and push the updated files to Crowdin')
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
        $crowdinClient = $this->getCrowdinClient($config);

        $executor = new Executor($logger);
        $cloner   = new ProjectCloner($executor);
        $filesProvider = new TranslationFilesProvider();

        $uploadOptions = $this->resolveCrowdinUploadOptions($config['crowdin']['upload']);
        $uploadBaseDir = $uploadOptions['base_dir'];
        $updateDir = $uploadBaseDir . '/update';

        $projectDir = $cloner->cloneProject($username, $updateDir, $edition);
        $files = $filesProvider->provideTranslations($projectDir);

        $translationUpdater = new TranslationFilesUpdater($crowdinClient, $logger);
        $translationUpdater->update($files);

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
    protected function resolveCrowdinUploadOptions(array $config)
    {
        $optionResolver = new OptionsResolver();
        $optionResolver->setRequired(['base_dir']);
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
