<?php

namespace Authters\Chronicle\Projection\Factory;

use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Projection\EventCounter;

abstract class PersistentProjectorContext extends ProjectorContext
{
    /**
     * @var EventCounter
     */
    protected $eventCounter;

    /**
     * @var bool
     */
    protected $streamCreated = false;

    public function __construct(EventStreamProvider $eventStreamProvider, PersistentProjectorOptions $options)
    {
        parent::__construct($eventStreamProvider, $options);

        $this->eventCounter = new EventCounter();
        $this->streamCreated = false;
    }

    public function eventCounter(): EventCounter
    {
        return $this->eventCounter;
    }

    public function isStreamCreated(): bool
    {
        return $this->streamCreated;
    }

    public function setStreamCreated(bool $streamCreated): void
    {
        $this->streamCreated = $streamCreated;
    }
}