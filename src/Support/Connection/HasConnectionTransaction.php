<?php

namespace Authters\Chronicle\Support\Connection;

use Authters\Chronicle\Exceptions\TransactionAlreadyStarted;
use Authters\Chronicle\Exceptions\TransactionNotStarted;
use Illuminate\Database\Connection;

trait HasConnectionTransaction
{
    /**
     * @throws TransactionAlreadyStarted
     */
    public function beginTransaction(): void
    {
        if ($this->isTransactionDisabled()) {
            return;
        }

        try {
            $this->connection()->beginTransaction();
        } catch (\Exception $exception) {
            throw new TransactionAlreadyStarted();
        }
    }

    /**
     * @throws TransactionNotStarted
     */
    public function commitTransaction(): void
    {
        if ($this->isTransactionDisabled()) {
            return;
        }

        try {
            $this->connection()->commit();
        } catch (\Exception $exception) {
            throw new TransactionNotStarted();
        }
    }

    /**
     * @param int|null $toLevel
     * @throws TransactionNotStarted
     */
    public function rollbackTransaction(int $toLevel = null): void
    {
        if ($this->isTransactionDisabled()) {
            return;
        }

        try {
            $this->connection()->rollBack($toLevel);
        } catch (\Exception $exception) {
            throw new TransactionNotStarted();
        }
    }

    public function inTransaction(): bool
    {
        return !$this->isTransactionDisabled() && $this->connection()->transactionLevel() > 0;
    }

    abstract protected function connection(): Connection;
}