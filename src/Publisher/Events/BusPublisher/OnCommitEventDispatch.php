<?php

namespace Authters\Chronicle\Publisher\Events\BusPublisher;

use Authters\Chronicle\Publisher\Events\Transaction\CommitTransaction;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalEventPublisher;
use Authters\Chronicle\Support\Publisher\CachedStreamEvents;
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
        return function (PublisherActionEvent $event) {
            if ($event->currentEvent()->target() instanceof TransactionalEventPublisher) {
                foreach ($this->cachedStreamEvents->streamEvents() as $recordedEvent) {
                    $this->eventBus->dispatch($recordedEvent);
                }

                $this->cachedStreamEvents->reset();
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new CommitTransaction();
    }

    public function priority(): int
    {
        return 1;
    }
}