<?php

namespace Authters\Chronicle\Publisher\Events\BusTransaction;

use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalEventPublisher;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalPublisher;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnMessageDispatchTransaction extends AbstractSubscriber
{
    /**
     * @var Publisher
     */
    private $publisher;

    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function applyTo(): callable
    {
        return function (MessageActionEvent $event) {
            if (!$this->publisher instanceof TransactionalEventPublisher) {
                return;
            }

            $this->publisher->beginTransaction();
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent;
    }

    public function priority(): int
    {
        return 1000;
    }
}