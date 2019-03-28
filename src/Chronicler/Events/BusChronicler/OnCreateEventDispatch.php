<?php

namespace Authters\Chronicle\Chronicler\Events\BusChronicler;

use Authters\Chronicle\Chronicler\Events\CreateEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalEventChronicler;
use Authters\Chronicle\Support\Publisher\CachedStreamEvents;
use Authters\ServiceBus\EventBus;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnCreateEventDispatch extends AbstractSubscriber
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
            $streamEvents = $event->stream()->streamEvents();

            if (!$this->inTransaction($event->currentEvent()->target())) {
                if (!$event->streamAlreadyExists()) {
                    foreach ($streamEvents as $recordedEvent) {
                        $this->eventBus->dispatch($recordedEvent);
                    }
                }
            } else {
                $this->cachedStreamEvents->add($streamEvents);
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new CreateEvent;
    }

    public function priority(): int
    {
        return 1;
    }

    private function inTransaction(Chronicler $publisher): bool
    {
        return $publisher instanceof TransactionalEventChronicler
            && $publisher->inTransaction();
    }
}