<?php

namespace Authters\Chronicle\Projection\Strategy;

use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\StreamNamingStrategy;

class AggregateNamingStrategy implements StreamNamingStrategy
{
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
        $prefix = $this->streamName ? $this->streamName->toString() : $aggregateType;

        return new StreamName($prefix . '-' . $aggregateId);
    }

    public function isOneStreamPerAggregate(): bool
    {
        return true;
    }
}