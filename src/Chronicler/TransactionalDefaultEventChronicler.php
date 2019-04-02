<?php

namespace Authters\Chronicle\Chronicler;

use Authters\Chronicle\Chronicler\Events\Transaction\BeginTransaction;
use Authters\Chronicle\Chronicler\Events\Transaction\CommitTransaction;
use Authters\Chronicle\Chronicler\Events\Transaction\RollbackTransaction;
use Authters\Chronicle\Chronicler\Tracker\TransactionalChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalChronicler;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalEventChronicler;

class TransactionalDefaultEventChronicler extends DefaultEventChronicler implements TransactionalEventChronicler
{
    /**
     * @var TransactionalChronicler
     */
    protected $publisher;

    public function beginTransaction(): void
    {
        /** @var TransactionalChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new BeginTransaction($this->publisher));

        $this->tracker->emit($event);

        if ($exception = $event->transactionAlreadyStarted()) {
            throw $exception;
        }
    }

    public function commitTransaction(): void
    {
        /** @var TransactionalChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new CommitTransaction($this->publisher));

        $this->tracker->emit($event);

        if ($exception = $event->transactionNotStarted()) {
            throw $exception;
        }
    }

    public function rollbackTransaction(int $toLevel = null): void
    {
        /** @var TransactionalChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new RollbackTransaction($this->publisher));

        $this->tracker->emit($event);

        if ($exception = $event->transactionNotStarted()) {
            throw $exception;
        }
    }

    public function inTransaction(): bool
    {
        return $this->publisher->inTransaction();
    }
}