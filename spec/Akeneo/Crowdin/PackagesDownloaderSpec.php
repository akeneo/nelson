<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Download;
use Akeneo\Crowdin\Api\Export;
use Akeneo\Crowdin\Client;
use Akeneo\Crowdin\PackagesDownloader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PackagesDownloaderSpec extends ObjectBehavior
{
    function let(Client $client, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(Argument::type(Event::class), Argument::type('string'))
            ->willReturn(new \stdClass());

        $this->beConstructedWith($client, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PackagesDownloader::class);
    }

    function it_download_every_locale(
        Client $client,
        Export $exportApi,
        Download $downloadApi
    ) {
        $client->api('export')->willReturn($exportApi);
        $exportApi->setBranch('master')->shouldBeCalled();
        $exportApi->execute()->shouldBeCalled();

        $client->api('download')->willReturn($downloadApi);
        $downloadApi->setBranch('master')->shouldBeCalled();
        $downloadApi->setCopyDestination('/tmp/')->shouldBeCalled()->willReturn($downloadApi);

        $downloadApi->setPackage('fr.zip')->shouldBeCalled()->willReturn($downloadApi);
        $downloadApi->setPackage('en.zip')->shouldBeCalled()->willReturn($downloadApi);
        $downloadApi->execute()->shouldBeCalled();

        $this->download(['fr', 'en'], '/tmp/', 'master');
    }
}
