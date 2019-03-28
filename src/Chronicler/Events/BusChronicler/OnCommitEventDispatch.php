<?php

namespace Authters\Chronicle\Chronicler\Events\BusChronicler;

use Authters\Chronicle\Chronicler\Events\Transaction\CommitTransaction;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalEventChronicler;
use Authters\Chronicle\Support\Chronicler\CachedStreamEvents;
use Authters\ServiceBus\EventBus;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnCommitEventDispatch extends AbstractSubscriber
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
            if ($event->currentEvent()->target() instanceof TransactionalEventChronicler) {
                foreach ($this->cachedStreamEvents->streamEvents() as $recordedEvent) {
                    $this->eventBus->dispatch($recordedEvent);
                }

                $this->cachedStreamEvents->reset();
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new CommitTransaction;
    }

    public function priority(): int
    {
        return 1;
    }
}