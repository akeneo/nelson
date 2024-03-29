<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\LanguageStatus;
use Akeneo\Crowdin\Api\Status;
use Akeneo\Crowdin\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class TranslatedProgressSelectorSpec extends ObjectBehavior
{
    const XML_STATUS = "
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

    const XML_LANGUAGE_STATUS = "
      <status>
        <files>
          <item>
            <node_type>branch</node_type>
            <id>29812</id>
            <name>master</name>
            <files>
              <item>
                <node_type>directory</node_type>
                <id>29827</id>
                <name>a_folder</name>
                <phrases>7</phrases>
                <translated>0</translated>
                <approved>100</approved>
                <words>32</words>
                <words_translated>0</words_translated>
                <words_approved>0</words_approved>
              </item>
            </files>
          </item>
        </files>
      </status>
    ";

    function let(Client $client, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(new Event());
        $this->beConstructedWith($client, $eventDispatcher, 50, ['a_folder'], ['master']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\TranslatedProgressSelector');
    }

    function it_displays_packages(
        Client $client,
        Status $statusApi,
        LanguageStatus $languageStatusApi,
    ) {
        $client->api('status')->willReturn($statusApi);
        $statusApi->execute()->willReturn(self::XML_STATUS);
        $client->api('language-status')->willReturn($languageStatusApi);

        $languageStatusApi->setLanguage('af')->willReturn($languageStatusApi)->shouldBeCalled();
        $languageStatusApi->setLanguage('fr')->willReturn($languageStatusApi)->shouldBeCalled();
        $languageStatusApi->execute()->willReturn(self::XML_LANGUAGE_STATUS);

        $this->display(new ConsoleOutput());
    }
}
