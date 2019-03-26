<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Projector;

interface Projector
{
    /**
     * @throws \Exception
     */
    public function reset(): void;

    /**
     * @throws \Exception
     */
    public function stop(): void;

    /**
     * @param bool $keepRunning
     * @throws \Exception
     */
    public function run(bool $keepRunning = true): void;

    /**
     * @return array
     */
    public function getState(): array;
}