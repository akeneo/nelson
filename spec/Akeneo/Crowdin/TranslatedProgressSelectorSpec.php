<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\LanguageStatus;
use Akeneo\Crowdin\Api\Status;
use Akeneo\Crowdin\Client;
use Akeneo\Crowdin\TranslatedProgressSelector;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        $this->beConstructedWith($client, $eventDispatcher, 50, ['a_folder'], ['master']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TranslatedProgressSelector::class);
    }

    function it_displays_packages(
        $client,
        OutputInterface $output,
        Status $statusApi,
        LanguageStatus $languageStatusApi,
        OutputFormatterInterface $formatter
    ) {
        $output->getFormatter()->willReturn($formatter);
        $output->write("Languages exported for master branch (50%):", true)->shouldBeCalled();
        $output->writeln(Argument::any())->shouldBeCalled();

        $client->api('status')->willReturn($statusApi);
        $statusApi->execute()->willReturn(self::XML_STATUS);

        $client->api('language-status')->willReturn($languageStatusApi);
        $languageStatusApi->setLanguage('af')->shouldBeCalled();
        $languageStatusApi->setLanguage('fr')->shouldBeCalled();
        $languageStatusApi->execute()->willReturn(self::XML_LANGUAGE_STATUS);

        $this->display($output);
    }
}
