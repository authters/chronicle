<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Strategy;

use Authters\Chronicle\Stream\StreamName;

interface StreamNamingStrategy
{
    public const DEFAULT_EVENT_STREAM_NAME = 'event_stream';

    public function determineStreamName(string $aggregateId, string $aggregateType): StreamName;

    public function isOneStreamPerAggregate(): bool;
}