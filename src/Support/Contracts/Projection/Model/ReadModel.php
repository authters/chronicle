<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Model;

interface ReadModel
{
    public function init(): void;

    public function isInitialized(): bool;

    public function reset(): void;

    public function delete(): void;

    public function stack(string $operation, ...$args): void;

    public function persist(): void;
}