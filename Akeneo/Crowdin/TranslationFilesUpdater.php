<?php

namespace Akeneo\Crowdin;

use Akeneo\TranslationFile;
use Crowdin\Client;
use Psr\Log\LoggerInterface;

/**
 * Class TranslationFilesUpdater
 *
 * @see https://crowdin.com/page/api/update-file limited to 20 files maximum per call
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class TranslationFilesUpdater
{
    /** @var int */
    const MAX_NB_FILES = 20;

    /** @var Client */
    protected $client;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param Client          $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param TranslationFile[] $files
     * @param string            $baseBranch
     */
    public function update(array $files, $baseBranch)
    {
        $fileSets = array_chunk($files, self::MAX_NB_FILES);

        foreach ($fileSets as $fileSet) {
            $service = $this->client->api('update-file');
            $service->setBranch($baseBranch);

            foreach ($fileSet as $file) {
                /** @var TranslationFile $file */
                $service->addTranslation($file->getSource(), $file->getTarget(), $file->getPattern());
                $this->logger->addInfo(sprintf('Push translation of "%s"', $file->getTarget()));
            }
            $service->execute();
        }
    }
}
