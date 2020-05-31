<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\UpdateFile;
use Akeneo\Crowdin\Client;
use Akeneo\Event\Events;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

class TranslationFilesUpdaterSpec extends ObjectBehavior
{
    function let(
        Client $client,
        EventDispatcherInterface $eventDispatcher,
        TargetResolver $resolver
    ) {
        $this->beConstructedWith($client, $eventDispatcher, $resolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\TranslationFilesUpdater');
    }

    function it_should_update_files(
        CLient $client,
        TargetResolver $resolver,
        EventDispatcherInterface $eventDispatcher,
        TranslationFile $file,
        UpdateFile $updateFileApi
    ) {
        $client->api('update-file')->willReturn($updateFileApi);
        $updateFileApi->setBranch('master')->shouldBeCalled();
        $file->getProjectDir()->willReturn('/tmp/');
        $file->getSource()->willReturn('/tmp/src/fr.yml');
        $file->getPattern()->willReturn('Project/src/fr.yml');
        $resolver->getTarget('/tmp/', '/tmp/src/fr.yml')->willReturn('fr.yml');

        $eventDispatcher->dispatch(Argument::type(Event::class), Events::PRE_CROWDIN_UPDATE_FILES)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(GenericEvent::class), Events::CROWDIN_UPDATE_FILE)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(Event::class), Events::POST_CROWDIN_UPDATE_FILES)->shouldBeCalled();

        $updateFileApi->addTranslation('/tmp/src/fr.yml', 'fr.yml', 'Project/src/fr.yml')->shouldBeCalled();
        $updateFileApi->getTranslations()->willReturn(['a_translation']);
        $updateFileApi->execute()->shouldBeCalled();

        $this->update([$file], 'master');
    }
}
