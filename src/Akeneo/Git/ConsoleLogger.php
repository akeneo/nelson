<?php

namespace Akeneo\Git;

use Akeneo\Event\Events;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber listening to Git events to display messages in console
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
            Events::PRE_GITHUB_CLONE       => 'preGithubClone',
            Events::POST_GITHUB_CLONE      => 'postGithubClone',
            Events::PRE_GITHUB_SET_BRANCH  => 'preGithubsetBranch',
            Events::POST_GITHUB_SET_BRANCH => 'postGithubsetBranch',
            Events::PRE_GITHUB_UPDATE      => 'preGithubUpdate',
            Events::POST_GITHUB_UPDATE     => 'postGithubUpdate',
            Events::PRE_GITHUB_CREATE_PR   => 'preGithubCreatePR',
            Events::POST_GITHUB_CREATE_PR  => 'postGithubCreatePR',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function preGithubClone(GenericEvent $event)
    {
        $this->writeProcessing(sprintf(
            'Cloning forked repository <bold>%s/%s</bold> into <bold>%s</bold>',
            $event->getArgument('fork_owner'),
            $event->getArgument('repository'),
            $event->getArgument('project_dir')
        ));
    }

    /**
     * @param Event $event
     */
    public function postGithubClone(Event $event)
    {
        $this->writeSuccess('Repository cloned!');
    }

    /**
     * @param GenericEvent $event
     */
    public function preGithubSetBranch(GenericEvent $event)
    {
        $this->writeProcessing(sprintf(
            'Create the git branch <bold>%s</bold> in the fork repository if it does not exist',
            $event->getArgument('branch')
        ));
    }

    /**
     * @param Event $event
     */
    public function postGithubSetBranch(Event $event)
    {
        $this->writeSuccess('Branch created or existing!');
    }

    /**
     * @param GenericEvent $event
     */
    public function preGithubUpdate(GenericEvent $event)
    {
        $this->writeProcessing(sprintf(
            'Updating forked repository of <bold>%s</bold> with latest updates of <bold>%s</bold>',
            $event->getArgument('repository'),
            $event->getArgument('owner')
        ));
    }

    /**
     * @param Event $event
     */
    public function postGithubUpdate(Event $event)
    {
        $this->writeSuccess('Repository updated!');
    }

    /**
     * @param GenericEvent $event
     */
    public function preGithubCreatePR(GenericEvent $event)
    {
        $this->writeProcessing(sprintf(
            'Creating Pull Request <bold>%s</bold> on branch <bold>%s</bold>',
            $event->getArgument('name'),
            $event->getArgument('branch')
        ));
    }

    /**
     * @param Event $event
     */
    public function postGithubCreatePR(Event $event)
    {
        $this->writeSuccess('Pull Request created!');
    }
}
