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
    private $cloner;
    private $projectInfo;
    private $directoriesCreator;
    private $filesCreator;
    private $filesUpdater;
    private $filesProvider;
    private $systemExecutor;
    private $eventDispatcher;

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
        $this->cloner = $cloner;
        $this->projectInfo = $projectInfo;
        $this->directoriesCreator = $directoriesCreator;
        $this->filesCreator = $filesCreator;
        $this->filesUpdater = $filesUpdater;
        $this->filesProvider = $filesProvider;
        $this->systemExecutor = $systemExecutor;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Push translation keys from Github to Crowdin.
     *
     * @param array $branches [githubBranch => crowdinFolder] or [branch]
     *                             where branch is the same name between Github and Crowdin folder
     */
    public function execute(array $branches, array $options)
    {
        $updateDir = $options['update_dir'];
        $isMapped = $this->isArrayAssociative($branches);

        foreach ($branches as $githubBranch => $crowdinFolder) {
            if (!$isMapped) {
                $githubBranch = $crowdinFolder;
            }
            $this->pushTranslations($githubBranch, $crowdinFolder, $options);
        }

        $this->systemExecutor->execute(sprintf('rm -rf %s', $updateDir));
    }

    /**
     * @param string $githubBranch
     * @param string $crowdinFolder
     * @param array  $options
     */
    protected function pushTranslations($githubBranch, $crowdinFolder, array $options)
    {
        $updateDir = $options['update_dir'];
        $dryRun = $options['dry_run'];

        $this->eventDispatcher->dispatch(
            Events::PRE_NELSON_PUSH,
            new GenericEvent($this, [
                'githubBranch' => (null === $githubBranch ? 'master' : $githubBranch),
                'crowdinFolder' => (null === $crowdinFolder ? 'master' : $crowdinFolder),
            ])
        );

        $projectDir = $this->cloner->cloneProject($updateDir, $githubBranch, $dryRun);
        $files = $this->filesProvider->provideTranslations($projectDir);
        $this->directoriesCreator->create($files, $this->projectInfo, $crowdinFolder, $dryRun);
        $this->filesCreator->create($files, $this->projectInfo, $crowdinFolder, $dryRun);
        $this->filesUpdater->update($files, $crowdinFolder, $dryRun);

        $this->eventDispatcher->dispatch(Events::POST_NELSON_PUSH);
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    protected function isArrayAssociative(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
