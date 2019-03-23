<?php

namespace Authters\Chronicle\Publisher\Tracker;

use Authters\Chronicle\Exceptions\TransactionAlreadyStarted;
use Authters\Chronicle\Exceptions\TransactionNotStarted;

class TransactionalPublisherActionEvent extends PublisherActionEvent
{
    /**
     * @var TransactionNotStarted
     */
    private $transactionNotStarted;

    /**
     * @var TransactionAlreadyStarted
     */
    private $transactionAlreadyStarted;

    public function setTransactionNotStarted(TransactionNotStarted $exception): void
    {
        $this->transactionNotStarted = $exception;
    }

    public function transactionNotStarted(): ?TransactionNotStarted
    {
        return $this->transactionNotStarted;
    }

    public function setTransactionAlreadyStarted(TransactionAlreadyStarted $exception): void
    {
        $this->transactionAlreadyStarted = $exception;
    }

    public function transactionAlreadyStarted(): ?TransactionNotStarted
    {
        return $this->transactionNotStarted;
    }
}