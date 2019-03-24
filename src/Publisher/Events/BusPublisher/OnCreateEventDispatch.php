<?php

namespace Authters\Chronicle\Publisher\Events\BusPublisher;

use Authters\Chronicle\Publisher\Events\CreateEvent;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalEventPublisher;
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
        return function (PublisherActionEvent $event) {
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
        return new CreateEvent();
    }

    public function priority(): int
    {
        return 1;
    }

    private function inTransaction(Publisher $publisher): bool
    {
        return $publisher instanceof TransactionalEventPublisher
            && $publisher->inTransaction();
    }
}