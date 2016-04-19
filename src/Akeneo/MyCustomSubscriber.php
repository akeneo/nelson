<?php

namespace Akeneo;

use Akeneo\Event\Events;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MyCustomSubscriber implements EventSubscriberInterface
{
    /** @var ConsoleOutputInterface */
    protected $output;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
        $formatter = $this->output->getFormatter();
        $formatter->setStyle('blink', new OutputFormatterStyle(null, null, array('blink')));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PRE_GITHUB_CLONE      => 'preGithubClone',
            Events::POST_GITHUB_CLONE     => 'postGithubClone',
            Events::PRE_GITHUB_TRACK      => 'preGithubTrack',
            Events::POST_GITHUB_TRACK     => 'postGithubTrack',
            Events::PRE_GITHUB_UPDATE     => 'preGithubUpdate',
            Events::POST_GITHUB_UPDATE    => 'postGithubUpdate',
            Events::PRE_GITHUB_CREATE_PR  => 'preGithubCreatePR',
            Events::POST_GITHUB_CREATE_PR => 'postGithubCreatePR',
            Events::PRE_CROWDIN_DOWNLOAD  => 'preCrowdinDownload',
            Events::POST_CROWDIN_DOWNLOAD => 'postCrowdinDownload',
            Events::PRE_CROWDIN_EXPORT    => 'preCrowdinExport',
            Events::POST_CROWDIN_EXPORT   => 'postCrowdinExport',
            Events::PRE_CROWDIN_CREATE_DIRECTORIES  => 'preCrowdinCreateDirectories',
            Events::CROWDIN_CREATE_DIRECTORY        => 'crowdinCreateDirectory',
            Events::CROWDIN_CREATE_BRANCH           => 'crowdinCreateBranch',
            Events::POST_CROWDIN_CREATE_DIRECTORIES => 'postCrowdinCreateDirectories',
            Events::PRE_CROWDIN_CREATE_FILES        => 'preCrowdinCreateFiles',
            Events::CROWDIN_CREATE_FILE             => 'crowdinCreateFile',
            Events::POST_CROWDIN_CREATE_FILES       => 'postCrowdinCreateFile',
            Events::PRE_CROWDIN_UPDATE_FILES        => 'preCrowdinUpdateFiles',
            Events::CROWDIN_UPDATE_FILE             => 'crowdinUpdateFile',
            Events::POST_CROWDIN_UPDATE_FILES       => 'postCrowdinUpdateFiles',
        ];
    }

    /**
     * @return string
     */
    protected function getTime()
    {
        return date('[H:i:s]');
    }

    /**
     * @param string $comment
     */
    public function writeComment($comment)
    {
        $this->output->writeln(sprintf('%s <comment>%s<blink>...</blink></comment>', $this->getTime(), $comment));
    }

    /**
     * @param string $success
     */
    public function writeSuccess($success)
    {
        $this->output->writeln(sprintf('%s <info>%s</info>', $this->getTime(), $success));
    }

    /**
     * @param Event $event
     */
    public function preGithubClone(Event $event)
    {
        $this->writeComment('Cloning forked repository');
    }

    /**
     * @param Event $event
     */
    public function postGithubClone(Event $event)
    {
        $this->writeSuccess('Repository cloned!');
    }

    /**
     * @param Event $event
     */
    public function preGithubTrack(Event $event)
    {
        $this->writeComment('Tracking forked repository to main repository');
    }

    /**
     * @param Event $event
     */
    public function postGithubTrack(Event $event)
    {
        $this->writeSuccess('Repository tracked!');
    }

    /**
     * @param Event $event
     */
    public function preGithubUpdate(Event $event)
    {
        $this->writeComment('Updating forked repository with latest updates');
    }

    /**
     * @param Event $event
     */
    public function postGithubUpdate(Event $event)
    {
        $this->writeSuccess('Repository updated!');
    }

    /**
     * @param Event $event
     */
    public function preGithubCreatePR(Event $event)
    {
        $this->writeComment('Creating Pull Request');
    }

    /**
     * @param Event $event
     */
    public function postGithubCreatePR(Event $event)
    {
        $this->writeSuccess('Pull Request created!');
    }

    /**
     * @param Event $event
     */
    public function preCrowdinDownload(Event $event)
    {
        $this->writeComment('Downloading packages from Crowdin');
    }

    /**
     * @param Event $event
     */
    public function postCrowdinDownload(Event $event)
    {
        $this->writeSuccess('Packages downloaded!');
    }

    /**
     * @param Event $event
     */
    public function preCrowdinExport(Event $event)
    {
        $this->writeComment('Exporting packages from Crowdin');
    }

    /**
     * @param Event $event
     */
    public function postCrowdinExport(Event $event)
    {
        $this->writeSuccess('Packages exported!');
    }

    public function preCrowdinCreateDirectories(Event $event)
    {
        $this->writeComment('Creating Crowdin directories');
    }

    public function crowdinCreateDirectory(Event $event)
    {
        $this->writeComment('Create directory');
    }

    public function crowdinCreateBranch(Event $event)
    {
        $this->writeComment('Create branch');
    }

    public function postCrowdinCreateDirectories(Event $event)
    {
        $this->writeSuccess('Crowdin directories created!');
    }

    public function preCrowdinCreateFiles(Event $event)
    {
        $this->writeComment('Creating files');
    }

    public function crowdinCreateFile(Event $event)
    {
        $this->writeComment('Create file');
    }

    public function postCrowdinCreateFiles(Event $event)
    {
        $this->writeSuccess('Files created!');
    }

    public function preCrowdinUpdateFiles(Event $event)
    {
        $this->writeComment('Updating files');
    }

    public function crowdinUpdateFile(Event $event)
    {
        $this->writeComment('Update file');
    }

    public function postCrowdinUpdateFiles(Event $event)
    {
        $this->writeSuccess('Files updated!');
    }
}
