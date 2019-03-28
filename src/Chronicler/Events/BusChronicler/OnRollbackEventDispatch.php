<?php

namespace Authters\Chronicle\Chronicler\Events\BusChronicler;

use Authters\Chronicle\Chronicler\Events\Transaction\RollbackTransaction;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Publisher\CachedStreamEvents;
use Authters\ServiceBus\EventBus;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnRollbackEventDispatch extends AbstractSubscriber
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var CachedStreamEvents
     */
    private $cachedStreamEvents;

    public function __construct(EventBus $eventBus, CachedStreamEvents $cachedStreamEvents)
    {
        $this->eventBus = $eventBus;
        $this->cachedStreamEvents = $cachedStreamEvents;
    }
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            $this->cachedStreamEvents->reset();
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new RollbackTransaction;
    }

    public function priority(): int
    {
        return 1;
    }
}