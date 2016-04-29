<?php

namespace Akeneo\Command;

use Akeneo\Nelson\PullTranslationsExecutor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clone the project in a temporary directory, download translations from Crowdin, clean the translation files, open
 * a pull request on Github
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PullTranslationsCommand extends ContainerAwareCommand
{
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registerSubscribers();

        $branches = $this->container->getParameter('github.branches');
        $options  = $this->container->getParameter('crowdin.download');
        $options['dry_run'] = $input->getOption('dry-run');

        /** @var PullTranslationsExecutor $executor */
        $executor = $this->container->get('nelson.pull_translations_executor');
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
