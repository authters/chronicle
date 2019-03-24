<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Projector\Projector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory as BaseProjectorFactory;

abstract class ProjectorFactory implements BaseProjectorFactory
{
    /**
     * @var ProjectorContextBuilder
     */
    protected $projectorBuilder;

    public function __construct(ProjectorContextBuilder $projectorBuilder)
    {
        $this->projectorBuilder = $projectorBuilder;
    }

    public function init(\Closure $callback): BaseProjectorFactory
    {
        $this->projectorBuilder->setInitCallback($callback);

        return $this;
    }

    public function fromStreams(string ...$streamNames): BaseProjectorFactory
    {
        $this->projectorBuilder->setStreamNames($streamNames);

        return $this;
    }

    public function when(iterable $handlers): Projector
    {
        $this->projectorBuilder->setHandlers($handlers);

        return $this->project();
    }

    /**
     * @return Projector
     */
    abstract protected function project();
}