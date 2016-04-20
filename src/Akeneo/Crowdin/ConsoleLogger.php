<?php

namespace Akeneo\Crowdin;

use Akeneo\Event\Events;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\GenericEvent;

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
            Events::PRE_CROWDIN_EXPORT              => 'preCrowdinExport',
            Events::CROWDIN_DOWNLOAD                => 'crowdinDownload',
            Events::POST_CROWDIN_EXPORT             => 'postCrowdinExport',
        ];
    }

    /**
     * @param Event $event
     */
    public function preCrowdinDownload(Event $event)
    {
        $this->writeProcessing('Downloading packages from Crowdin');
    }

    /**
     * @param GenericEvent $event
     */
    public function crowdinDownload(GenericEvent $event)
    {
        $this->writeInfo(sprintf('Download <bold>%s</bold> package', $event->getArgument('locale')));
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
        $this->writeProcessing('Exporting packages from Crowdin');
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
        $this->writeProcessing('Creating Crowdin directories');
    }

    /**
     * @param GenericEvent $event
     */
    public function crowdinCreateDirectory(GenericEvent $event)
    {
        $this->writeInfo(sprintf('Create directory <bold>%s</bold>', $event->getArgument('directory')));
    }

    /**
     * @param GenericEvent $event
     */
    public function crowdinCreateBranch(GenericEvent $event)
    {
        $this->writeInfo(sprintf('Create branch <bold>%s</bold>', $event->getArgument('branch')));
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
        $this->writeProcessing('Creating files');
    }

    /**
     * @param GenericEvent $event
     */
    public function crowdinCreateFile(GenericEvent $event)
    {
        $this->writeInfo(sprintf(
            'Create file <bold>%s</bold> from <bold>%s</bold>',
            $event->getArgument('target'),
            $event->getArgument('source')
        ));
    }

    /**
     * @param Event $event
     */
    public function postCrowdinCreateFile(Event $event)
    {
        $this->writeSuccess('Files created!');
    }

    /**
     * @param Event $event
     */
    public function preCrowdinUpdateFiles(Event $event)
    {
        $this->writeProcessing('Updating files');
    }

    /**
     * @param GenericEvent $event
     */
    public function crowdinUpdateFile(GenericEvent $event)
    {
        $this->writeInfo(sprintf('Update file <bold>%s</bold>', $event->getArgument('target')));
    }

    /**
     * @param Event $event
     */
    public function postCrowdinUpdateFiles(Event $event)
    {
        $this->writeSuccess('Files updated!');
    }
}
