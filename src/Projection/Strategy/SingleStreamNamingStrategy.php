<?php

namespace Authters\Chronicle\Projection\Strategy;

use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\StreamNamingStrategy;

class SingleStreamNamingStrategy implements StreamNamingStrategy
{
    /**
     * @var bool
     */
    private $oneStreamPerAggregate = false;

    /**
     * @var StreamName
     */
    private $streamName;

    public function __construct(StreamName $streamName = null)
    {
        $this->streamName = $streamName;
    }

    public function determineStreamName(string $aggregateId, string $aggregateType): StreamName
    {
        if (!$this->streamName) {
            return new StreamName(self::DEFAULT_EVENT_STREAM_NAME);
        }

        return $this->streamName;
    }

    public function isOneStreamPerAggregate(): bool
    {
        return $this->oneStreamPerAggregate;
    }

    public function streamName(): ?StreamName
    {
        return $this->streamName;
    }
}