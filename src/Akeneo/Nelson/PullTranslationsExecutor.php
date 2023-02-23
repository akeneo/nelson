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
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var Executor */
    private $systemExecutor;

    /** @var TranslationFilesCleaner */
    private $translationsCleaner;

    /** @var PackagesExtractor */
    private $extractor;

    /** @var TranslatedProgressSelector */
    private $status;

    /** @var PackagesDownloader */
    private $downloader;

    /** @var PullRequestCreator */
    private $pullRequestCreator;

    /** @var ProjectCloner */
    private $cloner;

    /** @var DiffChecker */
    private $diffChecker;

    /** @var PullRequestMerger */
    private $pullRequestMerger;

    public function __construct(
        ProjectCloner $cloner,
        PullRequestCreator $pullRequestCreator,
        PackagesDownloader $downloader,
        TranslatedProgressSelector $status,
        PackagesExtractor $extractor,
        TranslationFilesCleaner $translationsCleaner,
        Executor $systemExecutor,
        EventDispatcherInterface $eventDispatcher,
        DiffChecker $diffChecker,
        PullRequestMerger $pullRequestMerger
    ) {
        $this->cloner              = $cloner;
        $this->pullRequestCreator  = $pullRequestCreator;
        $this->downloader          = $downloader;
        $this->status              = $status;
        $this->extractor           = $extractor;
        $this->translationsCleaner = $translationsCleaner;
        $this->systemExecutor      = $systemExecutor;
        $this->eventDispatcher     = $eventDispatcher;
        $this->diffChecker = $diffChecker;
        $this->pullRequestMerger = $pullRequestMerger;
    }

    /**
     * Pull the translations from Crowdin to Github by creating Pull Requests
     *
     * @param array|null $branches The branches to manage.
     *                             [githubBranch => crowdinFolder] or [branch] or null
     *                             Where branch is the same name between Github and Crowdin folder
     * @param array      $options
     */
    public function execute($branches, array $options)
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

    /**
     * @param string $githubBranch
     * @param string $crowdinFolder
     * @param array  $packages
     * @param array  $options
     */
    protected function pullTranslations($githubBranch, $crowdinFolder, array $packages, array $options)
    {
        $updateDir  = $options['base_dir'] . '/update';
        $cleanerDir = $options['base_dir'] . '/clean';
        $dryRun     = isset($options['dry_run']) && $options['dry_run'];

        $this->eventDispatcher->dispatch(Events::PRE_NELSON_PULL, new GenericEvent($this, [
            'githubBranch'  => (null === $githubBranch ? 'master' : $githubBranch),
            'crowdinFolder' => (null === $crowdinFolder ? 'master' : $crowdinFolder)
        ]));

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
            if (null !== $pullRequest) {
                $this->pullRequestMerger->mergePullRequest($pullRequest);
            }
        }

        $this->eventDispatcher->dispatch(Events::POST_NELSON_PULL);
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
