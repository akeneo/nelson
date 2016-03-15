<?php

namespace Akeneo\System;

use Psr\Log\LoggerInterface;

/**
 * Class Executor
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
        try {
            system($command, $returnVar);
            $this->logger->info(sprintf('Executing command: %s (Result: %d)', $command, $returnVar));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error executing command: %s', $command));
            throw $exception;
        }
    }
}
