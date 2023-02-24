<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\UpdateFile;
use Akeneo\Crowdin\Client;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class TranslationFilesUpdaterSpec extends ObjectBehavior
{
    function let(
        Client $client,
        EventDispatcherInterface $eventDispatcher,
        TargetResolver $resolver
    ) {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(new Event());
        $this->beConstructedWith($client, $eventDispatcher, $resolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\TranslationFilesUpdater');
    }

    function it_should_update_files(
        $client,
        $resolver,
        TranslationFile $file,
        UpdateFile $updateFileApi
    ) {
        $client->api('update-file')->willReturn($updateFileApi);
        $updateFileApi->setBranch('master')->shouldBeCalled();
        $file->getProjectDir()->willReturn('/tmp/');
        $file->getSource()->willReturn('/tmp/src/fr.yml');
        $file->getPattern()->willReturn('Project/src/fr.yml');
        $resolver->getTarget('/tmp/', '/tmp/src/fr.yml')->willReturn('fr.yml');

        $updateFileApi->addTranslation('/tmp/src/fr.yml', 'fr.yml', 'Project/src/fr.yml')->shouldBeCalled();
        $updateFileApi->getTranslations()->willReturn(['a_translation']);
        $updateFileApi->execute()->shouldBeCalled();

        $this->update([$file], 'master');
    }
}
