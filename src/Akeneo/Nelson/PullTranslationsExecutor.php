<?php

namespace Akeneo\Nelson;

use Akeneo\Archive\PackagesExtractor;
use Akeneo\Crowdin\PackagesDownloader;
use Akeneo\Crowdin\TranslatedProgressSelector;
use Akeneo\Event\Events;
use Akeneo\Git\ProjectCloner;
use Akeneo\Git\PullRequestCreator;
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
    /**
     * @param ProjectCloner              $cloner
     * @param PullRequestCreator         $pullRequestCreator
     * @param PackagesDownloader         $downloader
     * @param TranslatedProgressSelector $status
     * @param PackagesExtractor          $extractor
     * @param TranslationFilesCleaner    $translationsCleaner
     * @param Executor                   $systemExecutor
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        ProjectCloner $cloner,
        PullRequestCreator $pullRequestCreator,
        PackagesDownloader $downloader,
        TranslatedProgressSelector $status,
        PackagesExtractor $extractor,
        TranslationFilesCleaner $translationsCleaner,
        Executor $systemExecutor,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->cloner              = $cloner;
        $this->pullRequestCreator  = $pullRequestCreator;
        $this->downloader          = $downloader;
        $this->status              = $status;
        $this->extractor           = $extractor;
        $this->translationsCleaner = $translationsCleaner;
        $this->systemExecutor      = $systemExecutor;
        $this->eventDispatcher     = $eventDispatcher;
    }

    /**
     * Pull the translations from Crowdin to Github by creating Pull Requests
     *
     * @param $branches
     * @param $options
     *
     * @throws \Exception
     */
    public function execute($branches, $options)
    {
        $updateDir  = $options['base_dir'] . '/update';
        $cleanerDir = $options['base_dir'] . '/clean';
        $packages   = array_keys($this->status->packages());
        $dryRun     = isset($options['dry_run']) && $options['dry_run'];

        if (count($packages) > 0) {
            foreach ($branches as $baseBranch) {
                $this->eventDispatcher->dispatch(Events::PRE_NELSON_PULL, new GenericEvent($this, [
                    'branch' => (null === $baseBranch) ? 'master' : $baseBranch
                ]));

                $projectDir = $this->cloner->cloneProject($updateDir, $baseBranch);
                $this->downloader->download($packages, $options['base_dir'], $baseBranch);
                $this->extractor->extract($packages, $options['base_dir'], $cleanerDir);
                $this->translationsCleaner->cleanFiles($options['locale_map'], $cleanerDir, $projectDir);
                $this->translationsCleaner->moveFiles($cleanerDir, $projectDir);

                if ($this->pullRequestCreator->haveDiff($projectDir)) {
                    $this->pullRequestCreator->create($baseBranch, $options['base_dir'], $projectDir, $dryRun);
                }

                $this->eventDispatcher->dispatch(Events::POST_NELSON_PULL);
            }

            $this->systemExecutor->execute(sprintf('rm -rf %s', $options['base_dir'] . '/update'));
        }
    }
}
