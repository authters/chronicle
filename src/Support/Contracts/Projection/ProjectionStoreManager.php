<?php

namespace Authters\Chronicle\Support\Contracts\Projection;

interface ProjectionStoreManager
{
    public function stopProjection(string $name): void;

    public function resetProjection(string $name): void;

    public function deleteProjection(string $name, bool $deleteEmittedEvents): void;
}