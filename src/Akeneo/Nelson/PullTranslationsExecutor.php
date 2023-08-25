<?php

namespace Akeneo\Nelson;

use Akeneo\Archive\PackagesExtractor;
use Akeneo\Crowdin\PackagesDownloader;
use Akeneo\Crowdin\TranslatedProgressSelector;
use Akeneo\Event\Events;
use Akeneo\Git\DiffChecker;
use Akeneo\Git\ProjectCloner;
use Akeneo\Git\PullRequestCreator;
use Akeneo\Git\PullRequestMerger;
use Akeneo\System\Executor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This class executes all the steps to pull translations from Crowdin to Github.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PullTranslationsExecutor
{
    public function __construct(
        private readonly ProjectCloner $cloner,
        private readonly PullRequestCreator $pullRequestCreator,
        private readonly PackagesDownloader $downloader,
        private readonly TranslatedProgressSelector $status,
        private readonly PackagesExtractor $extractor,
        private readonly TranslationFilesCleaner $translationsCleaner,
        private readonly Executor $systemExecutor,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DiffChecker $diffChecker,
        private readonly PullRequestMerger $pullRequestMerger
    ) {
    }

    /**
     * Pull the translations from Crowdin to Github by creating Pull Requests
     *
     * @param array|null $branches The branches to manage.
     *                             [githubBranch => crowdinFolder] or [branch] or null
     *                             Where branch is the same name between Github and Crowdin folder
     * @param array      $options
     */
    public function execute(?array $branches, array $options): void
    {
        $isMapped = $this->isArrayAssociative($branches);

        foreach ($branches as $githubBranch => $crowdinFolder) {
            if (!$isMapped) {
                $githubBranch = $crowdinFolder;
            }
            $packages = array_keys($this->status->packages(true, $crowdinFolder));

            if (count($packages) > 0) {
                $this->pullTranslations($githubBranch, $crowdinFolder, $packages, $options);
            }

            $this->systemExecutor->execute(sprintf('rm -rf %s', $options['base_dir'] . '/update'));
        }
    }

    protected function pullTranslations(
        ?string $githubBranch,
        ?string $crowdinFolder,
        array $packages,
        array $options
    ): void {
        $updateDir = $options['base_dir'] . '/update';
        $cleanerDir = $options['base_dir'] . '/clean';
        $dryRun = isset($options['dry_run']) && $options['dry_run'];

        $this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'githubBranch' => (null === $githubBranch ? 'master' : $githubBranch),
                'crowdinFolder' => (null === $crowdinFolder ? 'master' : $crowdinFolder),
            ]),
            Events::PRE_NELSON_PULL
        );

        $projectDir = $this->cloner->cloneProject($updateDir, $githubBranch, $dryRun);
        $this->downloader->download($packages, $options['base_dir'], $crowdinFolder);
        $this->extractor->extract($packages, $options['base_dir'], $cleanerDir);
        $this->translationsCleaner->cleanFiles(
            $options['locale_map'],
            $cleanerDir,
            $projectDir,
            $options['valid_locale_pattern'] ?? '/^.+$/'
        );
        $this->translationsCleaner->moveFiles($cleanerDir, $projectDir);

        if ($this->diffChecker->haveDiff($projectDir)) {
            $pullRequest = $this->pullRequestCreator->create($githubBranch, $options['base_dir'], $projectDir, $dryRun);
            if (!$dryRun && null !== $pullRequest) {
                $this->pullRequestMerger->mergePullRequest($pullRequest);
            }
        }

        $this->eventDispatcher->dispatch(new GenericEvent(), Events::POST_NELSON_PULL);
    }

    protected function isArrayAssociative(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
