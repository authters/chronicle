<?php

namespace Authters\Chronicle\Chronicler\Events\Transaction;

use Authters\Chronicle\Exceptions\TransactionNotStarted;
use Authters\Chronicle\Chronicler\Tracker\TransactionalChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalChronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnCommitTransaction extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (TransactionalChroniclerActionEvent $event) {
            try {
                /** @var TransactionalChronicler $publisher */
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