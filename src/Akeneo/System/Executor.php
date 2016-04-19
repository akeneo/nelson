<?php

namespace Akeneo\System;

use Psr\Log\LoggerInterface;

/**
 * Class Executor
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Executor
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $command
     *
     * @throws \Exception
     */
    public function execute($command)
    {
        $returnVar = null;
        $output = [];
        $command = sprintf('%s 2>&1', $command);
        try {
            $this->logger->info(sprintf('Executing command: %s', $command));
            exec($command, $output, $returnVar);
            $this->logger->debug($output);
            $this->logger->info(sprintf('Return code: %s', $returnVar));
        } catch (\Exception $exception) {
            $this->logger->info($output);
            $this->logger->error(sprintf('Error executing command: %s', $command));
            throw $exception;
        }
        if (0 !== $returnVar) {
            throw new \Exception(sprintf(
                "An error occurred during\n<comment>%s</comment>\n\n\n%s",
                $command,
                implode("\n", $output)
            ));
        }
    }
}
