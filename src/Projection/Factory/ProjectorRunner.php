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
     * @var ProjectorContext|PersistentProjectorContext
     */
    protected $context;

    /**
     * @var PersistentProjectorLock|null
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

    protected function handleStreamWithSingleHandler(string $streamName, \Iterator $events): void
    {
        $this->context->setStreamName($streamName);

        $handler = $this->context->singleHandler();

        foreach ($events as $key => $event) {
            if ($this->context->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }

            $this->context->streamPositions()->set($streamName, $key);

            if ($this->isPersistent()) {
                $this->context->eventCounter()->increment();
            }

            $result = $handler($this->context->state(), $event);

            $this->context->setState($result);

            if ($this->isPersistent()) {
                $this->resetEventCounter();
            }

            if ($this->context->isStopped()) {
                break;
            }
        }
    }

    protected function handleStreamWithHandlers(string $streamName, \Iterator $events): void
    {
        $this->context->setStreamName($streamName);

        $handlers = $this->context->handlers();

        foreach ($events as $key => $event) {
            if ($this->context->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }

            if ($this->isPersistent()) {
                $this->context->streamPositions()->set($streamName, $event->metadata()['_position']);
            } else {
                $this->context->streamPositions()->set($streamName, $key);
            }

            if (!isset($handlers[$event->messageName()])) {
                continue;
            }

            if ($this->isPersistent()) {
                $this->context->eventCounter()->increment();
            }

            $handler = $handlers[$event->messageName()];
            $result = $handler($this->context->state(), $event);
            $this->context->setState($result);

            if ($this->isPersistent()) {
                $this->resetEventCounter();
            }

            if ($this->context->isStopped()) {
                break;
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function prepareExecution(): void
    {
        if (!$this->isPersistent()) {
            return;
        }

        if (!$this->lock->projectionExists()) {
            $this->lock->createProjection();
        }

        $this->lock->acquireLock();

        $this->prepareStreamPositions();

        $this->lock->load();
    }

    private function resetEventCounter(): void
    {
        if (!$this->isPersistent()) {
            return;
        }

        if ($this->context->eventCounter()->isEqualsTo($this->context->options()->persistBlockSize)) {
            $this->lock->persist();

            $this->context->eventCounter()->reset();

            $this->context->setStatus(
                $this->lock->fetchRemoteStatus()
            );

            if (!$this->context->status()->is(ProjectionStatus::RUNNING())
                && !$this->context->status()->is(ProjectionStatus::IDLE())) {
                $this->context->stop(true);
            }
        }
    }

    abstract protected function isPersistent(): bool;
}