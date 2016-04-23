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

    /** @var string */
    protected $logFile;

    /**
     * @param LoggerInterface $logger
     * @param string          $logFile
     */
    public function __construct(LoggerInterface $logger, $logFile)
    {
        $this->logger  = $logger;
        $this->logFile = $this->getAbsolutePath($logFile);
    }

    /**
     * @param string $command
     *
     * @throws \Exception
     *
     * @return array
     */
    public function execute($command, $returnResult = false)
    {
        $returnVar = null;
        $output = [];
        $tmpFile = tempnam('/tmp/', 'crowdin.log');
        if ($returnResult) {
            $command = sprintf('%s > %s', $command, $tmpFile);
        } else {
            $command = sprintf('%s >> %s 2>&1', $command, $this->logFile);
        }
        try {
            $this->logger->info(sprintf('Executing command: %s', $command));
            exec($command, $output, $returnVar);
            $this->logger->info(sprintf('Return code: %s', $returnVar));
            if (0 !== $returnVar) {
                throw new \Exception(sprintf(
                    "An error occurred during\n<comment>%s</comment>\n\n\n%s",
                    $command,
                    implode("\n", $output)
                ));
            }

            if ($returnResult) {
                $result = file($tmpFile);
                unlink($tmpFile);

                return $result;
            }

            return $output;
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error executing command: %s', $command));
            throw $exception;
        }
    }

    /**
     * Allow user to put relative path, set it absolute from main directory.
     *
     * @param string $logFile
     *
     * @return string
     */
    protected function getAbsolutePath($logFile)
    {
        if (!preg_match('/^\//', $logFile)) {
            $logFile = sprintf('%s%s..%s..%s..%s%s',
                dirname(__FILE__),
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                $logFile
            );
        }

        return $logFile;
    }
}
