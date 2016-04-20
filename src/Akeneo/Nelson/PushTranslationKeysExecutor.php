<?php

namespace Akeneo\Nelson;

use Akeneo\Crowdin\TranslationDirectoriesCreator;
use Akeneo\Crowdin\TranslationFilesCreator;
use Akeneo\Crowdin\TranslationFilesUpdater;
use Akeneo\Crowdin\TranslationProjectInfo;
use Akeneo\Event\Events;
use Akeneo\Git\ProjectCloner;
use Akeneo\System\Executor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This class executes all the steps to push translation keys from Github to Crowdin.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PushTranslationKeysExecutor
{
    /**
     * @param ProjectCloner                 $cloner
     * @param TranslationProjectInfo        $projectInfo
     * @param TranslationDirectoriesCreator $directoriesCreator
     * @param TranslationFilesCreator       $filesCreator
     * @param TranslationFilesUpdater       $filesUpdater
     * @param TranslationFilesProvider      $filesProvider
     * @param Executor                      $systemExecutor
     * @param EventDispatcherInterface      $eventDispatcher
     */
    public function __construct(
        ProjectCloner $cloner,
        TranslationProjectInfo $projectInfo,
        TranslationDirectoriesCreator $directoriesCreator,
        TranslationFilesCreator $filesCreator,
        TranslationFilesUpdater $filesUpdater,
        TranslationFilesProvider $filesProvider,
        Executor $systemExecutor,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->cloner             = $cloner;
        $this->projectInfo        = $projectInfo;
        $this->directoriesCreator = $directoriesCreator;
        $this->filesCreator       = $filesCreator;
        $this->filesUpdater       = $filesUpdater;
        $this->filesProvider      = $filesProvider;
        $this->systemExecutor     = $systemExecutor;
        $this->eventDispatcher    = $eventDispatcher;
    }

    /**
     * Push translation keys from Github to Crowdin.
     *
     * @param array $branches
     * @param string $updateDir
     */
    public function execute($branches, $updateDir)
    {
        foreach ($branches as $baseBranch) {
            $this->eventDispatcher->dispatch(Events::PRE_NELSON_PUSH, new GenericEvent(null, [
                'branch' => $baseBranch
            ]));

            $projectDir = $this->cloner->cloneProject($updateDir, $baseBranch);
            $files = $this->filesProvider->provideTranslations($projectDir);
            $this->directoriesCreator->create($files, $this->projectInfo, $baseBranch);
            $this->filesCreator->create($files, $this->projectInfo, $baseBranch);
            $this->filesUpdater->update($files, $baseBranch);

            $this->eventDispatcher->dispatch(Events::POST_NELSON_PUSH);
        }

        $this->systemExecutor->execute(sprintf('rm -rf %s', $updateDir));
    }

}
