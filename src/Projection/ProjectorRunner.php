<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

abstract class ProjectorRunner
{
    /**
     * @var ProjectorConnector
     */
    protected $connector;

    /**
     * @var ProjectorContextBuilder
     */
    protected $builder;

    /**
     * @var ProjectorLock
     */
    protected $lock;

    /**
     * @var ProjectorMutable
     */
    protected $mutable;

    /**
     * @var ProjectorOptions
     */
    protected $options;

    protected function prepareStreamPositions(): void
    {
        if ($this->builder->isQueryCategories()) {
            $categories = $this->builder->queryCategories();
            $realStreamNames = $this->connector->eventStreamProvider()->findByCategories($categories);
        } elseif ($this->builder->isQueryAll()) {
            $realStreamNames = $this->connector->eventStreamProvider()->findAllExceptInternalStreams();
        } else {
            $realStreamNames = $this->builder->queryStreams();
        }

        $this->mutable->prepareStreamPositions($realStreamNames);
    }

    abstract protected function handleStreamWithSingleHandler(string $streamName, \Iterator $events): void;

    abstract protected function handleStreamWithHandlers(string $streamName, \Iterator $events): void;
}