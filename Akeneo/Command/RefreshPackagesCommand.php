<?php

namespace Akeneo\Command;

use Akeneo\Archive\Extractor;
use Akeneo\Archive\TranslationFilesCleaner;
use Akeneo\Crowdin\Downloader;
use Akeneo\Crowdin\PackagesBuilder;
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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Refresh the packages on Crowdin by forcing the build, allows to user to always download a "fresh" package from
 * Crowdin website
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RefreshPackagesCommand extends Command
{
    /** @var string */
    const LOG_FILE = 'refresh-packages.log';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:refresh-packages')
            ->setDescription('Refresh the packages on Crowdin by forcing the build');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->loadConfig();
        $logger = $this->getLogger($config);
        $crowdinClient = $this->getCrowdinClient($config);
        $packagesBuilder = new PackagesBuilder($crowdinClient);
        $packagesBuilder->build();

        $logger->addInfo('Crowdin packages have been built');
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
