<?php

namespace spec\Akeneo\Git;

use Github\Api\PullRequest;
use Github\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PullRequestMergerSpec extends ObjectBehavior
{
    public function let(
        Client $client,
        EventDispatcherInterface $eventDispatcher
    ) {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(new Event());
        $this->beConstructedWith($client, $eventDispatcher);
    }

    public function it_merges_a_pull_request(Client $client, PullRequest $githubMerger)
    {
        $client->api('pull_request')->willReturn($githubMerger);
        $githubMerger->merge('nelson', 'akeneo/repo', 78556, 'Merge pull request #78556')->shouldBeCalled();

        $this->mergePullRequest([
            'number' => 78556,
            'base' => [
                'user' => ['login' => 'nelson'],
                'repo' => ['name' => 'akeneo/repo'],
            ],
        ]);
    }
}
