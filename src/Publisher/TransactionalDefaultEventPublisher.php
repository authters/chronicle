<?php

namespace Authters\Chronicle\Publisher;

use Authters\Chronicle\Publisher\Events\Transaction\BeginTransaction;
use Authters\Chronicle\Publisher\Events\Transaction\CommitTransaction;
use Authters\Chronicle\Publisher\Events\Transaction\RollbackTransaction;
use Authters\Chronicle\Publisher\Tracker\TransactionalPublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalEventPublisher;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\TransactionalPublisher;

class TransactionalDefaultEventPublisher extends DefaultEventPublisher implements TransactionalEventPublisher
{
    /**
     * @var TransactionalPublisher
     */
    protected $publisher;

    public function beginTransaction(): void
    {
        /** @var TransactionalPublisherActionEvent $event */
        $event = $this->tracker->newActionEvent(new BeginTransaction($this->publisher));

        $this->tracker->emit($event);

        if($exception = $event->transactionAlreadyStarted()){
            throw $exception;
        }
    }

    public function commitTransaction(): void
    {
        /** @var TransactionalPublisherActionEvent $event */
        $event = $this->tracker->newActionEvent(new CommitTransaction($this->publisher));

        $this->tracker->emit($event);

        if($exception = $event->transactionNotStarted()){
            throw $exception;
        }
    }

    public function rollbackTransaction(int $toLevel = null): void
    {
        /** @var TransactionalPublisherActionEvent $event */
        $event = $this->tracker->newActionEvent(new RollbackTransaction($this->publisher));

        $this->tracker->emit($event);

        if($exception = $event->transactionNotStarted()){
            throw $exception;
        }
    }

    public function inTransaction(): bool
    {
        return $this->publisher->inTransaction();
    }
}