<?php

namespace Akeneo\Git;

use Akeneo\Event\Events;
use Github\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class PullRequestMerger
{
    public function __construct(
        protected Client $client,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function mergePullRequest(array $pullRequest): void
    {
        $this->waitForGithubCheckMergeableBranch();

        $mergeTitle = sprintf('Merge pull request #%s', $pullRequest['number']);

        $this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'number' => $pullRequest['number'],
            Events::PRE_GITHUB_MERGE_PR,
        );

        $this->client->api('pull_request')->merge(
            $pullRequest['base']['user']['login'],
            $pullRequest['base']['repo']['name'],
            $pullRequest['number'],
            $mergeTitle,
        );

	$this->eventDispatcher->dispatch(
            new GenericEvent($this, [
                'number' => $pullRequest['number'],
            ]),
            Events::POST_GITHUB_MERGE_PR,
        );
    }

    /**
     * @link https://github.community/t5/GitHub-API-Development-and/Merging-via-REST-API-returns-405-Base-branch-was-modified-Review/td-p/19281
     */
    private function waitForGithubCheckMergeableBranch(): void
    {
        sleep(3);
    }
}
