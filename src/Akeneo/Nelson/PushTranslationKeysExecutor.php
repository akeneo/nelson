<?php

namespace Akeneo\Nelson;

use Akeneo\Crowdin\TranslationDirectoriesCreator;
use Akeneo\Crowdin\TranslationFilesCreator;
use Akeneo\Crowdin\TranslationFilesUpdater;
use Akeneo\Crowdin\TranslationProjectInfo;
use Akeneo\Git\ProjectCloner;
use Akeneo\System\Executor;
use Akeneo\System\TranslationFilesProvider;

/**
 * TODO
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
     */
    public function __construct(
        ProjectCloner $cloner,
        TranslationProjectInfo $projectInfo,
        TranslationDirectoriesCreator $directoriesCreator,
        TranslationFilesCreator $filesCreator,
        TranslationFilesUpdater $filesUpdater,
        TranslationFilesProvider $filesProvider,
        Executor $systemExecutor
    ) {
        $this->cloner             = $cloner;
        $this->projectInfo        = $projectInfo;
        $this->directoriesCreator = $directoriesCreator;
        $this->filesCreator       = $filesCreator;
        $this->filesUpdater       = $filesUpdater;
        $this->filesProvider      = $filesProvider;
        $this->systemExecutor     = $systemExecutor;
    }

    /**
     * @param array $branches
     * @param string $updateDir
     */
    public function execute($branches, $updateDir)
    {
        foreach ($branches as $baseBranch) {
            $projectDir = $this->cloner->cloneProject($updateDir, $baseBranch);
            $files = $this->filesProvider->provideTranslations($projectDir);
            $this->directoriesCreator->create($files, $this->projectInfo, $baseBranch);
            $this->filesCreator->create($files, $this->projectInfo, $baseBranch);
            $this->filesUpdater->update($files, $baseBranch);
        }

        $this->systemExecutor->execute(sprintf('rm -rf %s', $updateDir));
    }

}
