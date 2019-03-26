<?php

namespace Authters\Chronicle\Projection\Factory;

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

    public function __construct(ProjectorOptions $options)
    {
        parent::__construct($options);

        $this->eventCounter = new EventCounter();
        $this->streamCreated = false;
    }

    public function eventCounter(): EventCounter
    {
        return $this->eventCounter;
    }
}