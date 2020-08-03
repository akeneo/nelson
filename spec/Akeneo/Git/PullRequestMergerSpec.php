<?php

namespace spec\Akeneo\Git;

use Akeneo\Event\Events;
use Github\Api\PullRequest;
use Github\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class PullRequestMergerSpec extends ObjectBehavior
{
    public function let(
        Client $client,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($client, $eventDispatcher);
    }

    public function it_merges_a_pull_request(
        Client $client,
        PullRequest $pullRequestApi,
        EventDispatcherInterface $eventDispatcher
    ) {
        $client->api('pull_request')->willReturn($pullRequestApi);
        $pullRequestApi->merge('nelson', 'akeneo/repo', 78556, 'Merge pull request #78556', 'sha12')->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), Events::PRE_GITHUB_MERGE_PR)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), Events::POST_GITHUB_MERGE_PR)->shouldBeCalled();

        $this->mergePullRequest([
            'number' => 78556,
            'merge_commit_sha' => 'sha12',
            'base' => [
                'user' => ['login' => 'nelson'],
                'repo' => ['name' => 'akeneo/repo'],
            ],
        ]);
    }
}
