<?php

namespace Authters\Chronicle\Support\Projection;

class StreamPositions
{
    /**
     * @var array
     */
    private $streamPositions;

    public function __construct(array $streamPositions = [])
    {
        $this->streamPositions = $streamPositions;
    }

    public function merge(array $streamPositions): void
    {
        $this->streamPositions = array_merge($streamPositions, $this->streamPositions);
    }

    public function set(string $streamName, int $position): void
    {
        $this->streamPositions[$streamName] = $position;
    }

    public function reset(): void
    {
        $this->streamPositions = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->streamPositions);
    }

    public function all(): array
    {
        return $this->streamPositions;
    }
}