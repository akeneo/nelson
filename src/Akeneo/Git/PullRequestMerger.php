<?php

namespace Akeneo\Git;

use Akeneo\Event\Events;
use Github\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class PullRequestMerger
{
    /** @var Client */
    private $client;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        Client $client,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function mergePullRequest(array $pullRequest): void
    {
        $mergeTitle = sprintf('Merge pull request #%s', $pullRequest['number']);

        $this->eventDispatcher->dispatch(Events::PRE_GITHUB_MERGE_PR, new GenericEvent($this, [
            'number' => $pullRequest['number'],
        ]));

        $this->client->api('pull_request')->merge(
            $pullRequest['base']['user']['login'],
            $pullRequest['base']['repo']['name'],
            $pullRequest['number'],
            $mergeTitle
        );

        $this->eventDispatcher->dispatch(Events::POST_GITHUB_MERGE_PR, new GenericEvent($this, [
            'number' => $pullRequest['number'],
        ]));
    }
}
