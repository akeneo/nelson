<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\AddDirectory;
use Akeneo\Crowdin\Client;
use Akeneo\Crowdin\TranslationProjectInfo;
use Akeneo\Nelson\TargetResolver;
use Akeneo\Nelson\TranslationFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class TranslationDirectoriesCreatorSpec extends ObjectBehavior
{
    function let(
        Client $client,
        LoggerInterface $logger,
        TargetResolver $resolver
    ) {
        $this->beConstructedWith($client, $logger, $resolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\TranslationDirectoriesCreator');
    }

    function it_should_create_a_branch_when_it_does_not_exist(
        $client,
        AddDirectory $addDirectoryApi,
        TranslationProjectInfo $projectInfo
    ) {
        $projectInfo->isBranchCreated('master')->willReturn(false);
        $client->api('add-directory')->willReturn($addDirectoryApi);
        $addDirectoryApi->setBranch('master')->shouldBeCalled();
        $projectInfo->getExistingFolders('master')->willReturn([]);

        $addDirectoryApi->setDirectory('master')->shouldBeCalled();
        $addDirectoryApi->setIsBranch(true)->shouldBeCalled();
        $addDirectoryApi->execute()->shouldBeCalled();

        $this->create([], $projectInfo, 'master');
    }

    function it_should_not_create_directory_when_it_exists(
        $client,
        $resolver,
        AddDirectory $addDirectoryApi,
        TranslationFile $file,
        TranslationProjectInfo $projectInfo
    ) {
        $client->api('add-directory')->willReturn($addDirectoryApi);
        $projectInfo->isBranchCreated('master')->willReturn(true);
        $addDirectoryApi->setBranch('master')->shouldBeCalled();
        $projectInfo->getExistingFolders('master')->willReturn(['src']);
        $file->getProjectDir()->willReturn('/tmp/');
        $file->getSource()->willReturn('/tmp/src/fr.yml');
        $resolver->getTargetDirectory('/tmp/', '/tmp/src/fr.yml')->willReturn('src');

        $addDirectoryApi->setDirectory('src')->shouldNotBeCalled();

        $this->create([$file], $projectInfo, 'master');
    }

    function it_should_create_directory_when_it_exists(
        $client,
        $resolver,
        AddDirectory $addDirectoryApi,
        TranslationFile $file,
        TranslationProjectInfo $projectInfo
    ) {
        $client->api('add-directory')->willReturn($addDirectoryApi);
        $projectInfo->isBranchCreated('master')->willReturn(true);
        $addDirectoryApi->setBranch('master')->shouldBeCalled();
        $projectInfo->getExistingFolders('master')->willReturn([]);
        $addDirectoryApi->execute()->shouldBeCalled();
        $file->getProjectDir()->willReturn('/tmp/');
        $file->getSource()->willReturn('/tmp/src/fr.yml');
        $resolver->getTargetDirectory('/tmp/', '/tmp/src/fr.yml')->willReturn('src');

        $addDirectoryApi->setDirectory('src')->shouldBeCalled();

        $this->create([$file], $projectInfo, 'master');
    }
}
