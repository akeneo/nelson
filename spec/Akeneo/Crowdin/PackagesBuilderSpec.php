<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Export;
use Akeneo\Crowdin\Client;
use Akeneo\Crowdin\PackagesBuilder;
use PhpSpec\ObjectBehavior;

class PackagesBuilderSpec extends ObjectBehavior
{
    function let(Client $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PackagesBuilder::class);
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
