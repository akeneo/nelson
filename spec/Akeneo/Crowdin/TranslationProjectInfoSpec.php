<?php

namespace spec\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\Info;
use Akeneo\Crowdin\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class TranslationProjectInfoSpec extends ObjectBehavior
{
    const XML = '<?xml version="1.0" encoding="ISO-8859-1"?>
    <info>
      <languages>
        <item>
          <name>Romanian</name>
          <code>ro</code>
          <can_translate>1</can_translate>
          <can_approve>1</can_approve>
        </item>
        <item>
          <name>French</name>
          <code>fr</code>
          <can_translate>1</can_translate>
          <can_approve>1</can_approve>
        </item>
      </languages>
      <files>
        <item>
          <node_type>branch</node_type>
          <name>master</name>
          <files>
            <item>
              <node_type>directory</node_type>
              <name>Project</name>
              <files>
                <item>
                  <node_type>directory</node_type>
                  <name>YoloBundle</name>
                  <files>
                    <item>
                      <node_type>file</node_type>
                      <name>validators.en.yml</name>
                      <created>2014-01-14 09:29:15</created>
                      <last_updated>2016-04-10 13:46:17</last_updated>
                      <last_accessed>2016-04-14 02:36:49</last_accessed>
                      <last_revision>10</last_revision>
                    </item>
                  </files>
                </item>
              </files>
            </item>
          </files>
        </item>
      </files>
      <details>
        <source_language>
          <name>English</name>
          <code>en</code>
        </source_language>
        <name>YourAwesomeProject</name>
        <identifier>yourawesomeproject</identifier>
        <created>2013-09-02 11:57:59</created>
        <description>A beautiful description</description>
        <join_policy>open</join_policy>
        <last_build>2016-04-16 14:33:22</last_build>
        <last_activity>2016-04-16 14:13:54</last_activity>
        <participants_count>187</participants_count>
        <total_strings_count>10058</total_strings_count>
        <total_words_count>36923</total_words_count>
        <duplicate_strings_count>8212</duplicate_strings_count>
        <duplicate_words_count>29265</duplicate_words_count>
        <invite_url>
          <translator>https://crowdin.com/project/akeneo/yourawesomeproject</translator>
          <proofreader>https://crowdin.com/project/akeneo/yourawesomeproject?d=123456789</proofreader>
        </invite_url>
      </details>
    </info>';

    function let(
        Client $client,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($client, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Crowdin\TranslationProjectInfo');
    }

    function it_returns_no_folders_when_there_is_no_branch(
        $client,
        Info $infoApi
    ) {
        $client->api('info')->willReturn($infoApi);
        $infoApi->execute()->willReturn(self::XML);

        $this->getExistingFolders('0.0')->shouldReturn(['']);
    }

    function it_returns_existing_folders(
        $client,
        Info $infoApi
    ) {
        $client->api('info')->willReturn($infoApi);
        $infoApi->execute()->willReturn(self::XML);

        $this->getExistingFolders('master')->shouldReturn([
            '',
            'Project',
            'Project/YoloBundle'
        ]);
    }

    function it_returns_no_files_when_there_is_no_branch(
        $client,
        Info $infoApi
    ) {
        $client->api('info')->willReturn($infoApi);
        $infoApi->execute()->willReturn(self::XML);

        $this->getExistingFiles('0.0')->shouldReturn([]);
    }

    function it_returns_existing_files(
        $client,
        Info $infoApi
    ) {
        $client->api('info')->willReturn($infoApi);
        $infoApi->execute()->willReturn(self::XML);

        $this->getExistingFiles('master')->shouldReturn(['Project/YoloBundle/validators.en.yml']);
    }

    function it_checks_branch_is_created(
        $client,
        Info $infoApi
    ) {
        $client->api('info')->willReturn($infoApi);
        $infoApi->execute()->willReturn(self::XML);

        $this->isBranchCreated('master')->shouldReturn(true);
    }

    function it_checks_branch_is_not_created(
        $client,
        Info $infoApi
    ) {
        $client->api('info')->willReturn($infoApi);
        $infoApi->execute()->willReturn(self::XML);

        $this->isBranchCreated('0.0')->shouldReturn(false);
    }
}
