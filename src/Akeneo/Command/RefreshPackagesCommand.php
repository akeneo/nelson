<?php

namespace Akeneo\Command;

use Akeneo\Crowdin\PackagesBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh the packages on Crowdin by forcing the build, allows to user to always download a "fresh" package from
 * Crowdin website
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshPackagesCommand extends Command
{
    /** @var PackagesBuilder */
    private $packagesBuilder;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(PackagesBuilder $packagesBuilder, LoggerInterface $logger)
    {
        parent::__construct();
        $this->packagesBuilder = $packagesBuilder;
        $this->logger = $logger;
    }

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
        $this->packagesBuilder->build();
        $this->logger->addInfo('Crowdin packages have been built');
    }
}
