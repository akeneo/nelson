<?php

namespace Akeneo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh the packages on Crowdin by forcing the build, allows to user to always download a "fresh" package from
 * Crowdin website
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RefreshPackagesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:refresh-packages')
            ->setDescription('Refresh the packages on Crowdin by forcing the build');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('crowdin.packages.builder')->build();
        $this->container->get('logger')->addInfo('Crowdin packages have been built');
    }
}
