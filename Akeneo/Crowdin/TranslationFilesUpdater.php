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
     */
    public function update(array $files)
    {
        $collections = $this->splitFilesList($files);
        foreach ($collections as $files) {
            $service = $this->client->api('update-file');
            foreach ($files as $file) {
                /** @var TranslationFile $file */
                $service->addTranslation($file->getSource(), $file->getTarget());
                $this->logger->addInfo(sprintf('Push file "%s" to "%s"', $file->getSource(), $file->getTarget()));
            }
            $service->execute();
        }
    }

    /**
     * Split files list to respect the max amount of files accepted by the api for each call
     *
     * @param TranslationFile[] $files
     *
     * @return array
     */
    protected function splitFilesList($files)
    {
        $collections = [];
        $counter = 0;
        $indCollection = 0;
        foreach ($files as $file) {
            if (!isset($collections[$indCollection])) {
                $collections[$indCollection] = [];
            }
            $collections[$indCollection][] = $file;
            $counter++;
            if (0 === $counter % self::MAX_NB_FILES) {
                $indCollection++;
            }
        }

        return $collections;
    }
}
