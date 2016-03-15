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
     * @param TranslationFile[]      $files
     * @param TranslationProjectInfo $projectInfo
     * @param string|null            $baseBranch
     */
    public function create(array $files, TranslationProjectInfo $projectInfo, $baseBranch = null)
    {
        $existingFiles = $projectInfo->getExistingFiles($baseBranch);
        $fileSets = array_chunk($this->filterExistingFiles($files, $existingFiles), self::MAX_UPLOAD);

        foreach ($fileSets as $fileSet) {
            /** @var AddFile $service */
            $service = $this->client->api('add-file');
            if (null !== $baseBranch) {
                $service->setBranch($baseBranch);
            }

            foreach ($fileSet as $file) {
                /** @var TranslationFile $file */
                $service->addTranslation($file->getSource(), $file->getTarget(), $file->getPattern());
                $this->logger->info(sprintf('Create file "%s"', $file->getTarget()));
            }
            $service->execute();
        }
    }

    /**
     * @param TranslationFile[] $files
     * @param string[]          $existingFiles
     *
     * @return TranslationFile[]
     */
    protected function filterExistingFiles($files, $existingFiles)
    {
        $result = [];

        foreach ($files as $file) {
            if (!in_array($file->getTarget(), $existingFiles)) {
                $result[] = $file;
            }
        }

        return $result;
    }
}
