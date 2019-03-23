<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Publisher;

interface TransactionalEventPublisher extends EventPublisher
{
    public function beginTransaction(): void;

    public function rollbackTransaction(int $toLevel = null): void;

    public function commitTransaction(): void;

    public function inTransaction(): bool;
}