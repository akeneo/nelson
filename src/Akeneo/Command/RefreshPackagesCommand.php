<?php

namespace Akeneo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh the packages on Crowdin by forcing the build, allows user to always download a "fresh" package from
 * Crowdin website
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshPackagesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nelson:refresh-packages')
            ->setDescription('Refresh the packages on Crowdin by forcing the build');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('logger')->info('Start Crowdin packages refres.');
        $this->container->get('crowdin.packages.builder')->build();
        $this->container->get('logger')->info('Crowdin packages have been built.');

        return 0;
    }
}
