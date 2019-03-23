<?php

namespace Authters\Chronicle\Publisher\Events\Transaction;

use Authters\Chronicle\Exceptions\TransactionAlreadyStarted;
use Authters\Chronicle\Publisher\Tracker\TransactionalPublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalPublisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnBeginTransaction extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (TransactionalPublisherActionEvent $event) {
            try {
                /** @var TransactionalPublisher $publisher */
                $publisher = $event->currentEvent()->target();
                $publisher->beginTransaction();
            } catch (TransactionAlreadyStarted $exception) {
                $event->setTransactionAlreadyStarted($exception);
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new BeginTransaction();
    }

    public function priority(): int
    {
        return 1;
    }
}