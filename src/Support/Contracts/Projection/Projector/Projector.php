<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Projector;

interface Projector
{
    public function reset(): void;

    public function stop(): void;

    /**
     * @param bool $keepRunning
     * @throws \Exception
     */
    public function run(bool $keepRunning = true): void;

    public function getState(): array;
}