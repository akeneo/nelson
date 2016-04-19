<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsoleLogger extends AbstractConsoleLogger
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
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

    /**
     * @param Event $event
     */
    public function preCrowdinCreateDirectories(Event $event)
    {
        $this->writeComment('Creating Crowdin directories');
    }

    /**
     * @param Event $event
     */
    public function crowdinCreateDirectory(Event $event)
    {
        $this->writeComment('Create directory');
    }

    /**
     * @param Event $event
     */
    public function crowdinCreateBranch(Event $event)
    {
        $this->writeComment('Create branch');
    }

    /**
     * @param Event $event
     */
    public function postCrowdinCreateDirectories(Event $event)
    {
        $this->writeSuccess('Crowdin directories created!');
    }

    /**
     * @param Event $event
     */
    public function preCrowdinCreateFiles(Event $event)
    {
        $this->writeComment('Creating files');
    }

    /**
     * @param Event $event
     */
    public function crowdinCreateFile(Event $event)
    {
        $this->writeComment('Create file');
    }

    /**
     * @param Event $event
     */
    public function postCrowdinCreateFiles(Event $event)
    {
        $this->writeSuccess('Files created!');
    }

    /**
     * @param Event $event
     */
    public function preCrowdinUpdateFiles(Event $event)
    {
        $this->writeComment('Updating files');
    }

    /**
     * @param Event $event
     */
    public function crowdinUpdateFile(Event $event)
    {
        $this->writeComment('Update file');
    }

    /**
     * @param Event $event
     */
    public function postCrowdinUpdateFiles(Event $event)
    {
        $this->writeSuccess('Files updated!');
    }
}
