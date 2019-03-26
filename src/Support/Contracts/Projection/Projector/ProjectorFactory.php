<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Projector;

interface ProjectorFactory extends Projector
{
    public function init(\Closure $callback): ProjectorFactory;

    // public function fromStream(string $streamName): ProjectorFactory;

    public function fromStreams(string ...$streamNames): ProjectorFactory;

    // public function fromCategory(string $name): ProjectorFactory;

    // public function fromCategories(string ...$names): ProjectorFactory;

    // public function fromAll(): ProjectorFactory;

    public function when(array $handlers): ProjectorFactory;

    //  public function whenAny(\Closure $closure): Projector;
}