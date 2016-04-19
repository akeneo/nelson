<?php

namespace Akeneo\Nelson;

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
            Events::PRE_NELSON_PULL  => 'preNelsonPull',
            Events::POST_NELSON_PULL => 'postNelsonPush',
            Events::PRE_NELSON_PUSH  => 'preNelsonPush',
            Events::POST_NELSON_PUSH => 'postNelsonPush',
        ];
    }

    /**
     * @param Event $event
     */
    public function preNelsonPull(Event $event)
    {
        $this->writeComment('Pulling translations from Crowdin to Github');
    }

    /**
     * @param Event $event
     */
    public function postNelsonPull(Event $event)
    {
        $this->writeSuccess('Translation pulled!');
    }

    /**
     * @param Event $event
     */
    public function preNelsonPush(Event $event)
    {
        $this->writeComment('Pushing translations from Github to Crowdin');
    }

    /**
     * @param Event $event
     */
    public function postNelsonPush(Event $event)
    {
        $this->writeSuccess('Translation pushed!');
    }
}
