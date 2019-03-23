<?php

namespace Authters\Chronicle\Stream;

class Stream
{
    /**
     * @var StreamName
     */
    protected $streamName;

    /**
     * @var iterable
     */
    protected $streamEvents;

    /**
     * @var array
     */
    protected $metadata = [];

    public function __construct(StreamName $streamName, \Iterator $streamEvents, array $metadata = [])
    {
        $this->streamName = $streamName;
        $this->streamEvents = $streamEvents;
        $this->metadata = $metadata;
    }

    public function streamName(): StreamName
    {
        return $this->streamName;
    }

    public function streamEvents(): \Iterator
    {
        return $this->streamEvents;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }
}