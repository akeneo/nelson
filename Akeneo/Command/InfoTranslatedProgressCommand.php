<?php

namespace Akeneo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Display the packages for next pull request
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class InfoTranslatedProgressCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:info-translated-progress')
            ->setDescription('Displays the languages that will be merged');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $this->container->getParameter('crowdin.download');

        $this->container
            ->get('crowdin.translated_progress.selector')
            ->display($output, $options['min_translated_progress']);
    }
}
