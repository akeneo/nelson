<?php

namespace Akeneo\Nelson;

use Akeneo\Event\Events;
use Akeneo\System\AbstractConsoleLogger;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

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

    public function preNelsonPull(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_NELSON_PULL, $event->getArguments());
    }

    public function postNelsonPull(Event $event)
    {
        $this->writeSuccess(Events::POST_NELSON_PULL);
    }

    public function preNelsonPush(GenericEvent $event)
    {
        $this->writeProcessing(Events::PRE_NELSON_PUSH, $event->getArguments());
    }

    public function postNelsonPush(Event $event)
    {
        $this->writeSuccess(Events::POST_NELSON_PUSH);
    }

    public function nelsonRename(GenericEvent $event)
    {
        $this->writeSuccess(Events::NELSON_RENAME, $event->getArguments());
    }

    public function nelsonDropUseless(GenericEvent $event)
    {
        $this->writeSuccess(Events::NELSON_DROP_USELESS, $event->getArguments());
    }
}
