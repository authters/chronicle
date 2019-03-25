<?php

namespace Authters\Chronicle\Projection\Factory;

use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

abstract class ProjectorRunner
{
    /**
     * @var ProjectorConnector
     */
    protected $connector;

    /**
     * @var ProjectorContext
     */
    protected $context;

    /**
     * @var ProjectorLock
     */
    protected $lock;

    protected function prepareStreamPositions(): void
    {
        if ($this->context->isQueryCategories()) {
            $categories = $this->context->queryCategories();
            $realStreamNames = $this->connector->eventStreamProvider()->findByCategories($categories);
        } elseif ($this->context->isQueryAll()) {
            $realStreamNames = $this->connector->eventStreamProvider()->findAllExceptInternalStreams();
        } else {
            $realStreamNames = $this->context->queryStreams();
        }

        $this->context->prepareStreamPositions($realStreamNames);
    }

    abstract protected function handleStreamWithSingleHandler(string $streamName, \Iterator $events): void;

    abstract protected function handleStreamWithHandlers(string $streamName, \Iterator $events): void;
}