<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Download;
use Akeneo\Crowdin\Api\Export;
use Akeneo\Crowdin\Client;
use Akeneo\Event\Events;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

class PackagesDownloaderSpec extends ObjectBehavior
{
    function let(Client $client, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($client, $eventDispatcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\PackagesDownloader');
    }

    function it_download_every_locale(
        Client $client,
        EventDispatcherInterface $eventDispatcher,
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

        $eventDispatcher->dispatch(Argument::type(Event::class), Events::PRE_CROWDIN_DOWNLOAD)
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(Event::class), Events::POST_CROWDIN_DOWNLOAD)
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), Events::CROWDIN_DOWNLOAD)
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(Event::class), Events::PRE_CROWDIN_EXPORT)
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(Event::class), Events::POST_CROWDIN_EXPORT)
            ->shouldBeCalled();

        $this->download(['fr', 'en'], '/tmp/', 'master');
    }
}
