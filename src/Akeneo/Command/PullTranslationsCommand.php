<?php

namespace Akeneo\Command;

use Akeneo\Nelson\PullTranslationsExecutor;
use Akeneo\System\AbstractConsoleLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Clone the project in a temporary directory, download translations from Crowdin, clean the translation files, open
 * a pull request on Github
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PullTranslationsCommand extends Command
{
    /** @var PullTranslationsExecutor */
    private $pullTranslationsExecutor;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var AbstractConsoleLogger */
    private $nelsonLogger;
    /** @var AbstractConsoleLogger */
    private $githubLogger;
    /** @var AbstractConsoleLogger */
    private $crowdinLogger;
    /** @var array */
    private $crowdinDownloadConfig;
    /** @var array */
    private $githubBranches;

    public function __construct(
        PullTranslationsExecutor $pullTranslationsExecutor,
        EventDispatcherInterface $eventDispatcher,
        AbstractConsoleLogger $nelsonLogger,
        AbstractConsoleLogger $githubLogger,
        AbstractConsoleLogger $crowdinLogger,
        array $crowdinDownloadConfig,
        array $githubBranches
    ) {
        parent::__construct();
        $this->pullTranslationsExecutor = $pullTranslationsExecutor;
        $this->eventDispatcher = $eventDispatcher;
        $this->nelsonLogger = $nelsonLogger;
        $this->githubLogger = $githubLogger;
        $this->crowdinLogger = $crowdinLogger;
        $this->crowdinDownloadConfig = $crowdinDownloadConfig;
        $this->githubBranches = $githubBranches;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nelson:pull-translations')
            ->setDescription('Fetch new translations from Crowdin and create pull requests to the Github repository')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, "Don't create pull requests in Github");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->registerSubscribers();

        $options = $this->crowdinDownloadConfig;
        $options['dry_run'] = $input->getOption('dry-run');

        $this->pullTranslationsExecutor->execute($this->githubBranches, $options);

        return Command::SUCCESS;
    }

    /**
     * Manually register subscribers for event dispatcher
     */
    protected function registerSubscribers()
    {
        $this->eventDispatcher->addSubscriber($this->nelsonLogger);
        $this->eventDispatcher->addSubscriber($this->githubLogger);
        $this->eventDispatcher->addSubscriber($this->crowdinLogger);
    }
}
