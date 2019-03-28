<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Chronicler;

interface TransactionalEventChronicler extends EventChronicler
{
    public function beginTransaction(): void;

    public function rollbackTransaction(int $toLevel = null): void;

    public function commitTransaction(): void;

    public function inTransaction(): bool;
}