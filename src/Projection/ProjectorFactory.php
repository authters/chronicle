<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Projection\Factory\ProjectorContext;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory as Factory;

abstract class ProjectorFactory implements Factory
{
    /**
     * @var ProjectorContext
     */
    protected $context;

    public function __construct(ProjectorContext $projectorBuilder)
    {
        $this->context = $projectorBuilder;
    }

    public function init(\Closure $callback): Factory
    {
        $this->context->setInitCallback($callback);

        return $this;
    }

    public function fromStream(string $streamName, MetadataMatcher $metadataMatcher = null): Factory
    {
        $this->context->setFrom('streams', [$streamName]);

        $this->context->setMetadataMatcher($metadataMatcher);

        return $this;
    }

    public function fromStreams(string ...$streamNames): Factory
    {
        $this->context->setFrom('streams', $streamNames);

        return $this;
    }

    public function fromCategory(string $name): Factory
    {
        $this->context->setFrom('categories', [$name]);

        return $this;
    }

    public function fromCategories(string ...$names): Factory
    {
        $this->context->setFrom('categories', $names);

        return $this;
    }

    public function fromAll(): Factory
    {
        $this->context->setFrom('all');

        return $this;
    }


    public function whenAny(\Closure $callback): Factory
    {
        $this->context->setHandlers($callback);

        return $this;
    }

    public function when(iterable $handlers): Factory
    {
        $this->context->setHandlers($handlers);

        return $this;
    }
}