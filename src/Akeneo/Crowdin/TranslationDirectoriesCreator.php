<?php

namespace Akeneo\Crowdin;

use Akeneo\Crowdin\Api\AddDirectory;
use Akeneo\Event\Events;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var TargetResolver */
    protected $targetResolver;

    /**
     * @param Client                   $client
     * @param EventDispatcherInterface $eventDispatcher
     * @param TargetResolver           $targetResolver
     */
    public function __construct(
        Client $client,
        EventDispatcherInterface $eventDispatcher,
        TargetResolver $targetResolver
    ) {
        $this->client          = $client;
        $this->eventDispatcher = $eventDispatcher;
        $this->targetResolver  = $targetResolver;
    }

    /**
     * Create the new folders in Crowdin.
     * If baseBranch is specified, create the folders into the specific branch node.
     *
     * @param TranslationFile[]      $files
     * @param TranslationProjectInfo $projectInfo
     * @param string                 $baseBranch
     * @param boolean                $dryRun
     */
    public function create(array $files, TranslationProjectInfo $projectInfo, $baseBranch, $dryRun = false)
    {
        $this->eventDispatcher->dispatch(Events::PRE_CROWDIN_CREATE_DIRECTORIES);

        /** @var AddDirectory $service */
        $service = $this->client->api('add-directory');
        $this->createBranchIfNotExists($baseBranch, $projectInfo);
        $service->setBranch($baseBranch);

        $existingFolders = $projectInfo->getExistingFolders($baseBranch);
        foreach ($this->getDirectoriesFromFiles($files) as $directory) {
            if (!in_array($directory, $existingFolders) && !in_array($directory, ['/', ''])) {
                $this->eventDispatcher->dispatch(Events::CROWDIN_CREATE_DIRECTORY, new GenericEvent($this, [
                    'directory' => $directory,
                    'dry_run'   => $dryRun
                ]));
                if (!$dryRun) {
                    $service->setDirectory($directory);
                    $service->execute();
                }
            }
        }

        $this->eventDispatcher->dispatch(Events::POST_CROWDIN_CREATE_DIRECTORIES);
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
        $allDirs = [''];
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
            $this->eventDispatcher->dispatch(Events::CROWDIN_CREATE_BRANCH, new GenericEvent($this, [
                'branch' => $baseBranch
            ]));

            /** @var AddDirectory $serviceBranch */
            $serviceBranch = $this->client->api('add-directory');
            $serviceBranch->setDirectory($baseBranch);
            $serviceBranch->setIsBranch(true);

            $serviceBranch->execute();
        }
    }
}
