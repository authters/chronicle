<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Projector;

interface PersistentProjector extends Projector
{
    public function delete(bool $deleteEmittedEvents): void;

    public function getName(): string;
}