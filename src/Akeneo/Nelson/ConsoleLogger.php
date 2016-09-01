<?php

namespace Akeneo\Nelson;

use Akeneo\Event\Events;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber listening to Nelson events to display messages in console
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
            Events::PRE_NELSON_PULL     => 'preNelsonPull',
            Events::POST_NELSON_PULL    => 'postNelsonPush',
            Events::PRE_NELSON_PUSH     => 'preNelsonPush',
            Events::POST_NELSON_PUSH    => 'postNelsonPush',
            Events::NELSON_RENAME       => 'nelsonRename',
            Events::NELSON_DROP_USELESS => 'nelsonDropUseless',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function preNelsonPull(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_NELSON_PULL, $event->getArguments());
    }

    /**
     * @param Event $event
     */
    public function postNelsonPull(Event $event)
    {
        $this->writeSuccess(Events::POST_NELSON_PULL);
    }

    /**
     * @param GenericEvent $event
     */
    public function preNelsonPush(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_NELSON_PUSH, $event->getArguments());
    }

    /**
     * @param Event $event
     */
    public function postNelsonPush(Event $event)
    {
        $this->writeSuccess(Events::POST_NELSON_PUSH);
    }

    /**
     * @param GenericEvent $event
     */
    public function nelsonRename(GenericEvent $event)
    {
        $this->writeSuccess(Events::NELSON_RENAME, $event->getArguments());
    }

    /**
     * @param GenericEvent $event
     */
    public function nelsonDropUseless(GenericEvent $event)
    {
        $this->writeSuccess(Events::NELSON_DROP_USELESS, $event->getArguments());
    }
}
