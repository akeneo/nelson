<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\AddDirectory;
use Akeneo\System\TargetResolver;
use Akeneo\System\TranslationFile;
use Psr\Log\LoggerInterface;

/**
 * This class creates all the missing directories of a Crowdin project.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationDirectoriesCreator
{
    /** @var Client */
    protected $client;

    /** @var LoggerInterface */
    protected $logger;

    /** @var TargetResolver */
    protected $targetResolver;

    /**
     * @param Client          $client
     * @param LoggerInterface $logger
     * @param TargetResolver  $targetResolver
     */
    public function __construct(Client $client, LoggerInterface $logger, TargetResolver $targetResolver)
    {
        $this->client         = $client;
        $this->logger         = $logger;
        $this->targetResolver = $targetResolver;
    }

    /**
     * Create the new folders in Crowdin.
     * If baseBranch is specified, create the folders into the specific branch node.
     *
     * @param TranslationFile[]      $files
     * @param TranslationProjectInfo $projectInfo
     * @param string                 $baseBranch
     */
    public function create(array $files, TranslationProjectInfo $projectInfo, $baseBranch)
    {
        /** @var AddDirectory $service */
        $service = $this->client->api('add-directory');
        $this->createBranchIfNotExists($baseBranch, $projectInfo);
        $this->logger->info(sprintf('Use branch "%s"', $baseBranch));
        $service->setBranch($baseBranch);

        $existingFolders = $projectInfo->getExistingFolders($baseBranch);
        foreach ($this->getDirectoriesFromFiles($files) as $directory) {
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
            $allDirs = array_merge($allDirs, $this->explodeDirectory(
                $this->targetResolver->getTargetDirectory(
                    $file->getProjectDir(),
                    $file->getSource()
                )
            ));
        }
        $allDirs = array_unique($allDirs);
        sort($allDirs);

        return $allDirs;
    }

    /**
     * Creates the root node for the branch if not exists.
     *
     * @param string                 $baseBranch
     * @param TranslationProjectInfo $projectInfo
     */
    protected function createBranchIfNotExists($baseBranch, $projectInfo)
    {
        if (!$projectInfo->isBranchCreated($baseBranch)) {
            $this->logger->info(sprintf('Create branch "%s"', $baseBranch));

            /** @var AddDirectory $serviceBranch */
            $serviceBranch = $this->client->api('add-directory');
            $serviceBranch->setDirectory($baseBranch);
            $serviceBranch->setIsBranch(true);

            $serviceBranch->execute();
        }
    }
}
