<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Projector\Projector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory as BaseProjectorFactory;

abstract class ProjectorFactory implements BaseProjectorFactory
{
    /**
     * @var ProjectorContext
     */
    protected $projectorContext;

    public function __construct(ProjectorContext $projectorBuilder)
    {
        $this->projectorContext = $projectorBuilder;
    }

    public function init(\Closure $callback): BaseProjectorFactory
    {
        $this->projectorContext->setInitCallback($callback);

        return $this;
    }

    public function fromStreams(string ...$streamNames): BaseProjectorFactory
    {
        $this->projectorContext->setStreamNames($streamNames);

        return $this;
    }

    public function when(iterable $handlers): Projector
    {
        $this->projectorContext->setHandlers($handlers);

        return $this->project();
    }

    /**
     * @return Projector
     */
    abstract protected function project();
}