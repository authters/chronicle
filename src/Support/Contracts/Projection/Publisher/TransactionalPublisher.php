<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Publisher;

interface TransactionalPublisher extends Publisher
{
    public function beginTransaction(): void;

    public function rollbackTransaction(int $toLevel = null): void;

    public function commitTransaction(): void;

    public function inTransaction(): bool;
}