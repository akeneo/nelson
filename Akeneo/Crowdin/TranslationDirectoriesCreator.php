<?php

namespace Akeneo\Crowdin;

use Akeneo\TranslationFile;
use Crowdin\Api\AddDirectory;
use Crowdin\Client;
use Psr\Log\LoggerInterface;

/**
 * This class creates all the missing directories of a Crowdin project.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class TranslationDirectoriesCreator
{
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
     * Create the new folders in Crowdin.
     *
     * @param TranslationFile[] $files
     * @param string[]          $existingFolders
     */
    public function create(array $files, array $existingFolders = [])
    {
        /** @var AddDirectory $service */
        $service = $this->client->api('add-directory');
        foreach($this->getDirectoriesFromFiles($files) as $directory) {
            if (in_array($directory, $existingFolders)) {
                $this->logger->info(sprintf('Existing directory "%s"', $directory));
            } else {
                $service->setDirectory($directory);
                $this->logger->info(sprintf('Create directory "%s"', $directory));
                $service->execute();
            }
        }
    }

    /**
     * Returns all the paths composing the directory.
     *
     * For example, the result of explodeDirectory("PimCommunity/BatchBundle/") will be "PimCommunity" and
     * "PimCommunity/BatchBundle".
     *
     * @param string $dir
     *
     * @return string[]
     */
    protected function explodeDirectory($dir)
    {
        $directories = [];
        $folders = explode('/', $dir);
        $currentPath = null;
        foreach ($folders as $folder) {
            $currentPath = null === $currentPath ? $folder : sprintf('%s/%s', $currentPath, $folder);
            $directories[] = $currentPath;
        }

        return $directories;
    }

    /**
     * Returns all the directories from a set of files.
     *
     * For example, with a set of files having target directories like
     * - PimCommunity/BatchBundle/validators.yml
     * - PimCommunity/BatchBundle/messages.yml
     * - PimCommunity/CatalogBundle/messages.yml
     *
     * The result would be
     * - PimCommunity
     * - PimCommunity/BatchBundle
     * - PimCommunity/CatalogBundle
     *
     * @param TranslationFile[] $files
     *
     * @return string[]
     */
    protected function getDirectoriesFromFiles($files)
    {
        $allDirs = [];
        foreach ($files as $file) {
            $allDirs = array_merge($allDirs, $this->explodeDirectory($file->getTargetDirectory()));
        }
        $allDirs = array_unique($allDirs);
        sort($allDirs);

        return $allDirs;
    }
}
