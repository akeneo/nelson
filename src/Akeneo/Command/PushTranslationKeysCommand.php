<?php

namespace Akeneo\Command;

use Akeneo\Nelson\PushTranslationKeysExecutor;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Clone the project in a temporary directory, push any translation file (only english) to Crowdin
 *
 * Note that this command will not create new files on Crowdin
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PushTranslationKeysCommand extends Command
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var AbstractConsoleLogger */
    private $nelsonLogger;
    /** @var AbstractConsoleLogger */
    private $githubLogger;
    /** @var AbstractConsoleLogger */
    private $crowdinLogger;
    /** @var PushTranslationKeysExecutor */
    private $pushTranslationKeysExecutor;
    /** @var array */
    private $crowdinUploadConfig;
    /** @var array */
    private $githubBranches;

    public function __construct(
        PushTranslationKeysExecutor $pushTranslationKeysExecutor,
        EventDispatcherInterface $eventDispatcher,
        AbstractConsoleLogger $nelsonLogger,
        AbstractConsoleLogger $githubLogger,
        AbstractConsoleLogger $crowdinLogger,
        array $crowdinUploadConfig,
        array $githubBranches
    ) {
        parent::__construct();
        $this->eventDispatcher = $eventDispatcher;
        $this->nelsonLogger = $nelsonLogger;
        $this->githubLogger = $githubLogger;
        $this->crowdinLogger = $crowdinLogger;
        $this->pushTranslationKeysExecutor = $pushTranslationKeysExecutor;
        $this->crowdinUploadConfig = $crowdinUploadConfig;
        $this->githubBranches = $githubBranches;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nelson:push-translation-keys')
            ->setDescription('Fetch new translation keys from Github and push the updated files to Crowdin')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, "Don't create directories, files nor update it");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registerSubscribers();

        $options = [
            'update_dir' => $this->crowdinUploadConfig['base_dir'] . '/update',
            'dry_run' => $input->getOption('dry-run'),
        ];

        $this->pushTranslationKeysExecutor->execute($this->githubBranches, $options);
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
