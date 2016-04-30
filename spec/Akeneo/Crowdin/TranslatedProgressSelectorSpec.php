<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Status;
use Akeneo\Crowdin\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TranslatedProgressSelectorSpec extends ObjectBehavior
{
    const XML = "
      <status>
          <language>
            <name>Afrikaans</name>
            <code>af</code>
            <phrases>10057</phrases>
            <translated>0</translated>
            <approved>0</approved>
            <words>36922</words>
            <words_translated>0</words_translated>
            <words_approved>0</words_approved>
            <translated_progress>10</translated_progress>
            <approved_progress>0</approved_progress>
          </language>
          <language>
            <name>French</name>
            <code>fr</code>
            <phrases>10057</phrases>
            <translated>0</translated>
            <approved>0</approved>
            <words>36922</words>
            <words_translated>0</words_translated>
            <words_approved>0</words_approved>
            <translated_progress>90</translated_progress>
            <approved_progress>0</approved_progress>
          </language>
        </status>";

    function let(Client $client, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($client, $eventDispatcher, 50);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\TranslatedProgressSelector');
    }

    function it_displays_packages(
        $client,
        OutputInterface $output,
        Status $statusApi
    ) {
        $client->api('status')->willReturn($statusApi);
        $statusApi->execute()->willReturn(self::XML);
        $output->write('fr (90%)', true)->shouldBeCalled();
        $output->write('af (10%)', true)->shouldNotBeCalled();
        $this->display($output);
    }
}
