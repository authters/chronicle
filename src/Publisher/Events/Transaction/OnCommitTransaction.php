<?php

namespace Authters\Chronicle\Publisher\Events\Transaction;

use Authters\Chronicle\Exceptions\TransactionNotStarted;
use Authters\Chronicle\Publisher\Tracker\TransactionalPublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalPublisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnCommitTransaction extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (TransactionalPublisherActionEvent $event) {
            try {
                /** @var TransactionalPublisher $publisher */
                $publisher = $event->currentEvent()->target();
                $publisher->commitTransaction();
            } catch (TransactionNotStarted $exception) {
                $event->setTransactionNotStarted($exception);
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