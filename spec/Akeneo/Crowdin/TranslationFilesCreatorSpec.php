<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\AddFile;
use Akeneo\Crowdin\Client;
use Akeneo\Crowdin\TranslationProjectInfo;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TranslationFilesCreatorSpec extends ObjectBehavior
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
        $this->shouldHaveType('Akeneo\Crowdin\TranslationFilesCreator');
    }

    function it_should_create_file_when_it_does_not_exist(
        $client,
        $resolver,
        TranslationFile $file,
        TranslationProjectInfo $projectInfo,
        AddFile $addFileApi
    ) {
        $client->api('add-file')->willReturn($addFileApi);
        $projectInfo->getExistingFiles('master')->willReturn([]);
        $file->getProjectDir()->willReturn('/tmp/');
        $file->getSource()->willReturn('/tmp/src/fr.yml');
        $file->getPattern()->willReturn('Project/src/fr.yml');
        $resolver->getTarget('/tmp/', '/tmp/src/fr.yml')->willReturn('fr.yml');

        $addFileApi->setBranch('master')->shouldBeCalled();
        $addFileApi->addTranslation('/tmp/src/fr.yml', 'fr.yml', 'Project/src/fr.yml')->shouldBeCalled();
        $addFileApi->execute()->shouldBeCalled();

        $this->create([$file], $projectInfo, 'master');
    }

    function it_should_not_create_file_when_it_exists(
        $client,
        $resolver,
        TranslationFile $file,
        TranslationProjectInfo $projectInfo,
        AddFile $addFileApi
    ) {
        $client->api('add-file')->willReturn($addFileApi);
        $projectInfo->getExistingFiles('master')->willReturn(['fr.yml']);
        $file->getProjectDir()->willReturn('/tmp/');
        $file->getSource()->willReturn('/tmp/src/fr.yml');
        $file->getPattern()->willReturn('Project/src/fr.yml');
        $resolver->getTarget('/tmp/', '/tmp/src/fr.yml')->willReturn('fr.yml');

        $addFileApi->addTranslation('/tmp/src/fr.yml', 'fr.yml', 'Project/src/fr.yml')->shouldNotBeCalled();

        $this->create([$file], $projectInfo, 'master');
    }
}
