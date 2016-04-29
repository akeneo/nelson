<?php

namespace Akeneo\Command;

use Akeneo\Nelson\PushTranslationKeysExecutor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clone the project in a temporary directory, push any translation file (only english) to Crowdin
 *
 * Note that this command will not create new files on Crowdin
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PushTranslationKeysCommand extends ContainerAwareCommand
{
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
            'update_dir' => $this->container->getParameter('crowdin.upload')['base_dir'] . '/update',
            'dry_run'    => $input->getOption('dry-run')
        ];
        $branches  = $this->container->getParameter('github.branches');

        /** @var PushTranslationKeysExecutor $executor */
        $executor = $this->container->get('nelson.push_translation_keys_executor');
        $executor->execute($branches, $options);
    }

    /**
     * Manually register subscribers for event dispatcher
     */
    protected function registerSubscribers()
    {
        $eventDispatcher = $this->container->get('event_dispatcher');
        $eventDispatcher->addSubscriber($this->container->get('nelson.console_logger'));
        $eventDispatcher->addSubscriber($this->container->get('github.console_logger'));
        $eventDispatcher->addSubscriber($this->container->get('crowdin.console_logger'));
    }
}
