<?php

namespace Akeneo\Git;

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
            Events::PRE_GITHUB_CLONE                => 'preGithubClone',
            Events::POST_GITHUB_CLONE               => 'postGithubClone',
            Events::PRE_GITHUB_TRACK                => 'preGithubTrack',
            Events::POST_GITHUB_TRACK               => 'postGithubTrack',
            Events::PRE_GITHUB_UPDATE               => 'preGithubUpdate',
            Events::POST_GITHUB_UPDATE              => 'postGithubUpdate',
            Events::PRE_GITHUB_CREATE_PR            => 'preGithubCreatePR',
            Events::POST_GITHUB_CREATE_PR           => 'postGithubCreatePR',
        ];
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
}
