<?php

namespace Akeneo\Crowdin;

use Akeneo\TranslationFile;
use Crowdin\Api\AddFile;
use Crowdin\Client;
use Psr\Log\LoggerInterface;

/**
 * This class creates all the missing files of a Crowdin project.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class TranslationFilesCreator
{
    const MAX_UPLOAD = 10;

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
     * Create the missing files in Crowdin. Set it by batch using MAX_UPLOAD const.
     *
     * @param TranslationFile[] $files
     * @param string[]          $existingFiles
     */
    public function create(array $files, array $existingFiles = [])
    {
        $count = 0;
        $service = $this->client->api('add-file');

        foreach ($files as $file) {
            if (in_array($file->getTarget(), $existingFiles)) {
                $this->logger->info(sprintf('Existing file "%s"', $file->getTarget()));
            } else {
                /** @var AddFile $service */
                $service->addTranslation($file->getSource(), $file->getTarget(), $file->getPattern());
                $this->logger->info(sprintf('Create file "%s"', $file->getTarget()));
                $count ++;
                if ($count >= self::MAX_UPLOAD) {
                    $service->execute();
                    $service = $this->client->api('add-file');
                    $count = 0;
                }
            }
        }
        if ($count > 0) {
            $service->execute();
        }
    }
}
