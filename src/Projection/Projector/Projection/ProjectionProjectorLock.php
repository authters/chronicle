<?php

namespace Authters\Chronicle\Projection\Projector\Projection;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Projection\Factory\PersistentProjectorLock;
use Authters\Chronicle\Projection\Factory\ProjectorContext;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;

class ProjectionProjectorLock extends PersistentProjectorLock
{
    /**
     * @var Chronicler
     */
    private $chronicler;

    public function __construct(Chronicler $chronicler,
                                ProjectionProvider $projectionProvider,
                                ProjectorContext $context,
                                string $name)
    {
        parent::__construct($projectionProvider, $context, $name);

        $this->chronicler = $chronicler;
    }

    public function reset(): void
    {
        parent::reset();

        try {
            $this->chronicler->delete(new StreamName($this->name));
        } catch (StreamNotFound $exception) {
        }
    }

    protected function deleteEmittedEvents(): void
    {
        try {
            $this->chronicler->delete(new StreamName($this->name));
        } catch (StreamNotFound $exception) {
        }
    }
}