<?php

namespace Authters\Chronicle\Projection\Projector\Projection;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Projection\Factory\PersistentProjectorLock;
use Authters\Chronicle\Projection\Factory\ProjectorContext;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

class ProjectionProjectorLock extends PersistentProjectorLock
{
    /**
     * @var Publisher
     */
    private $publisher;

    public function __construct(Publisher $publisher,
                                ProjectionProvider $projectionProvider,
                                ProjectorContext $context,
                                string $name)
    {
        parent::__construct($projectionProvider, $context, $name);

        $this->publisher = $publisher;
    }

    /**
     * @throws \Exception
     */
    public function reset(): void
    {
        parent::reset();

        try {
            $this->publisher->delete(new StreamName($this->name));
        } catch (StreamNotFound $exception) {
        }
    }

    /**
     * @throws \Exception
     */
    protected function deleteEmittedEvents(): void
    {
        try {
            $this->publisher->delete(new StreamName($this->name));
        } catch (StreamNotFound $exception) {
        }
    }
}