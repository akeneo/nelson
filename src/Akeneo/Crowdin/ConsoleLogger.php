<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Subscriber listening to Crowdin events to display messages in console
 *
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
            Events::PRE_CROWDIN_CREATE_DIRECTORIES => 'preCrowdinCreateDirectories',
            Events::CROWDIN_CREATE_DIRECTORY => 'crowdinCreateDirectory',
            Events::CROWDIN_CREATE_BRANCH => 'crowdinCreateBranch',
            Events::POST_CROWDIN_CREATE_DIRECTORIES => 'postCrowdinCreateDirectories',
            Events::PRE_CROWDIN_CREATE_FILES => 'preCrowdinCreateFiles',
            Events::CROWDIN_CREATE_FILE => 'crowdinCreateFile',
            Events::POST_CROWDIN_CREATE_FILES => 'postCrowdinCreateFile',
            Events::PRE_CROWDIN_UPDATE_FILES => 'preCrowdinUpdateFiles',
            Events::CROWDIN_UPDATE_FILE => 'crowdinUpdateFile',
            Events::POST_CROWDIN_UPDATE_FILES => 'postCrowdinUpdateFiles',
            Events::PRE_CROWDIN_EXPORT => 'preCrowdinExport',
            Events::CROWDIN_DOWNLOAD => 'crowdinDownload',
            Events::POST_CROWDIN_EXPORT => 'postCrowdinExport',
            Events::PRE_CROWDIN_PACKAGES => 'preCrowdinPackages',
            Events::POST_CROWDIN_PACKAGES => 'postCrowdinPackages',
        ];
    }

    public function preCrowdinDownload(Event $event)
    {
        $this->writeProcessing(Events::PRE_CROWDIN_DOWNLOAD);

        return $event;
    }

    public function crowdinDownload(GenericEvent $event)
    {
        $this->writeInfo(Events::CROWDIN_DOWNLOAD, $event->getArguments());

        return $event;
    }

    public function postCrowdinDownload(Event $event)
    {
        $this->writeSuccess(Events::POST_CROWDIN_DOWNLOAD);

        return $event;
    }

    public function preCrowdinExport(Event $event)
    {
        $this->writeProcessing(Events::PRE_CROWDIN_EXPORT);

        return $event;
    }

    public function postCrowdinExport(Event $event)
    {
        $this->writeSuccess(Events::POST_CROWDIN_EXPORT);

        return $event;
    }

    public function preCrowdinCreateDirectories(Event $event)
    {
        $this->writeProcessing(Events::PRE_CROWDIN_CREATE_DIRECTORIES);

        return $event;
    }

    public function crowdinCreateDirectory(GenericEvent $event)
    {
        $this->writeInfo(Events::CROWDIN_CREATE_DIRECTORY, $event->getArguments());

        return $event;
    }

    public function crowdinCreateBranch(GenericEvent $event)
    {
        $this->writeInfo(Events::CROWDIN_CREATE_BRANCH, $event->getArguments());

        return $event;
    }

    public function postCrowdinCreateDirectories(Event $event)
    {
        $this->writeSuccess(Events::POST_CROWDIN_CREATE_DIRECTORIES);

        return $event;
    }

    public function preCrowdinCreateFiles(Event $event)
    {
        $this->writeProcessing(Events::PRE_CROWDIN_CREATE_FILES);

        return $event;
    }

    public function crowdinCreateFile(GenericEvent $event)
    {
        $this->writeInfo(Events::CROWDIN_CREATE_FILE, $event->getArguments());

        return $event;
    }

    public function postCrowdinCreateFile(Event $event)
    {
        $this->writeSuccess(Events::POST_CROWDIN_CREATE_FILES);

        return $event;
    }

    public function preCrowdinUpdateFiles(Event $event)
    {
        $this->writeProcessing(Events::PRE_CROWDIN_UPDATE_FILES);

        return $event;
    }

    public function crowdinUpdateFile(GenericEvent $event)
    {
        $this->writeInfo(Events::CROWDIN_UPDATE_FILE, $event->getArguments());

        return $event;
    }

    public function postCrowdinUpdateFiles(Event $event)
    {
        $this->writeSuccess(Events::POST_CROWDIN_UPDATE_FILES);

        return $event;
    }

    public function preCrowdinPackages(Event $event)
    {
        $this->writeProcessing(Events::PRE_CROWDIN_PACKAGES);

        return $event;
    }

    public function postCrowdinPackages(GenericEvent $event)
    {
        $this->writeSuccess(Events::POST_CROWDIN_PACKAGES, $event->getArguments());

        return $event;
    }
}
