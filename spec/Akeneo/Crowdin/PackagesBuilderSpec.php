<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Export;
use Akeneo\Crowdin\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PackagesBuilderSpec extends ObjectBehavior
{
    function let(Client $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\PackagesBuilder');
    }

    function it_builds_package(
        $client,
        Export $exportApi
    ) {
        $client->api('export')->willReturn($exportApi);
        $exportApi->execute()->shouldBeCalled();
        $this->build();
    }
}
