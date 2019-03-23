<?php

namespace Authters\Chronicle\Support\Publisher;

class CachedStreamEvents
{
    /**
     * @var array
     */
    private $streamEvents = [];

    public function add(iterable $streamEvents): void
    {
        $this->streamEvents[] = $streamEvents;
    }

    public function reset(): void
    {
        $this->streamEvents = [];
    }

    public function streamEvents(): iterable
    {
        return $this->streamEvents;
    }
}