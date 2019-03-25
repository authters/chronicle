<?php

namespace Authters\Chronicle\Projection\Projector\Query;

use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector as BaseProjector;

class QueryProjector implements BaseProjector
{

    public function reset(): void
    {
        // TODO: Implement reset() method.
    }

    public function stop(): void
    {
        // TODO: Implement stop() method.
    }

    /**
     * @param bool $keepRunning
     * @throws \Exception
     */
    public function run(bool $keepRunning = true): void
    {
        // TODO: Implement run() method.
    }

    public function getState(): array
    {
        // TODO: Implement getState() method.
    }
}