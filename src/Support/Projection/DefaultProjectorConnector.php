<?php

namespace Authters\Chronicle\Support\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

class DefaultProjectorConnector implements ProjectorConnector
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var ProjectionProvider
     */
    private $projectionProvider;

    /**
     * @var EventStreamProvider
     */
    private $eventStreamProvider;

    public function __construct(Publisher $publisher,
                                ProjectionProvider $projectionProvider,
                                EventStreamProvider $eventStreamProvider)
    {
        $this->publisher = $publisher;
        $this->projectionProvider = $projectionProvider;
        $this->eventStreamProvider = $eventStreamProvider;
    }

    public function publisher(): Publisher
    {
        return $this->publisher;
    }

    public function projectionProvider(): ProjectionProvider
    {
        return $this->projectionProvider;
    }

    public function eventStreamProvider(): EventStreamProvider
    {
        return $this->eventStreamProvider;
    }
}