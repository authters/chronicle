<?php

namespace Authters\Chronicle\Chronicler\Events\Transaction;

use Authters\Chronicle\Exceptions\TransactionAlreadyStarted;
use Authters\Chronicle\Chronicler\Tracker\TransactionalChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalChronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnBeginTransaction extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (TransactionalChroniclerActionEvent $event) {
            try {
                /** @var TransactionalChronicler $publisher */
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