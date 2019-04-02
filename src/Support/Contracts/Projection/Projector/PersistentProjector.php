<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Projector;

interface PersistentProjector extends Projector
{
    /**
     * @param bool $deleteEmittedEvents
     * @throws \Exception
     */
    public function delete(bool $deleteEmittedEvents): void;

    public function getName(): string;
}