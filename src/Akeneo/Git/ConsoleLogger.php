<?php

namespace Akeneo\Git;

use Akeneo\Event\Events;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

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
            Events::PRE_GITHUB_CLONE => 'preGithubClone',
            Events::POST_GITHUB_CLONE => 'postGithubClone',
            Events::PRE_GITHUB_SET_BRANCH => 'preGithubsetBranch',
            Events::POST_GITHUB_SET_BRANCH => 'postGithubsetBranch',
            Events::PRE_GITHUB_UPDATE => 'preGithubUpdate',
            Events::POST_GITHUB_UPDATE => 'postGithubUpdate',
            Events::PRE_GITHUB_CREATE_PR => 'preGithubCreatePR',
            Events::POST_GITHUB_CREATE_PR => 'postGithubCreatePR',
            Events::PRE_GITHUB_CHECK_DIFF => 'preGithubCheckDiff',
            Events::POST_GITHUB_CHECK_DIFF => 'postGithubCheckDiff',
            Events::PRE_GITHUB_MERGE_PR => 'preGithubMergePR',
            Events::POST_GITHUB_MERGE_PR => 'postGithubMergePR',
        ];
    }

    public function preGithubClone(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_GITHUB_CLONE, $event->getArguments());

        return $event;
    }

    public function postGithubClone(Event $event)
    {
        $this->writeSuccess(Events::POST_GITHUB_CLONE);

        return $event;
    }

    public function preGithubSetBranch(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_GITHUB_SET_BRANCH, $event->getArguments());

        return $event;
    }

    public function postGithubSetBranch(Event $event)
    {
        $this->writeSuccess(Events::POST_GITHUB_SET_BRANCH);

        return $event;
    }

    public function preGithubUpdate(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_GITHUB_UPDATE, $event->getArguments());

        return $event;
    }

    public function postGithubUpdate(Event $event)
    {
        $this->writeSuccess(Events::POST_GITHUB_UPDATE);

        return $event;
    }

    public function preGithubCreatePR(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_GITHUB_CREATE_PR, $event->getArguments());

        return $event;
    }

    public function postGithubCreatePR(Event $event)
    {
        $this->writeSuccess(Events::POST_GITHUB_CREATE_PR);

        return $event;
    }

    public function preGithubCheckDiff(Event $event)
    {
        $this->writeProcessing(Events::PRE_GITHUB_CHECK_DIFF);

        return $event;
    }

    public function postGithubCheckDiff(GenericEvent $event)
    {
        $this->writeSuccess(sprintf('%s difference(s) found!', $event->getArgument('diff')));

        return $event;
    }

    public function preGithubMergePR(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_GITHUB_MERGE_PR, $event->getArguments());

        return $event;
    }

    public function postGithubMergePR(GenericEvent $event)
    {
        $this->writeProcessing(Events::POST_GITHUB_MERGE_PR, $event->getArguments());

        return $event;
    }
}
