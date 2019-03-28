<?php

namespace Authters\Chronicle\Projection\Factory;

use Authters\Chronicle\Projection\Projector\Query\QueryProjectorContext;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

abstract class ProjectorRunner
{
    /**
     * @var EventStreamProvider
     */
    protected $eventStreamProvider;

    /**
     * @var Publisher
     */
    protected $publisher;

    /**
     * @var ProjectorContext|PersistentProjectorContext|QueryProjectorContext
     */
    protected $context;

    /**
     * @var PersistentProjectorLock|null
     */
    protected $lock;

    public function __construct(ProjectorContext $context,
                                EventStreamProvider $eventStreamProvider,
                                Publisher $publisher,
                                PersistentProjectorLock $lock = null)
    {
        $this->context = $context;
        $this->eventStreamProvider = $eventStreamProvider;
        $this->publisher = $publisher;
        $this->lock = $lock;
    }

    // checkMe move the bloc to context?
    protected function prepareStreamPositions(): void
    {
         if ($this->context->isQueryCategories()) {
            $categories = $this->context->queryCategories();
            $realStreamNames = $this->eventStreamProvider
                ->findByCategories($categories)
                ->toArray();
        } elseif ($this->context->isQueryAll()) {
            $realStreamNames = $this->eventStreamProvider
                ->findAllExceptInternalStreams()
                ->pluck('real_stream_name')
                ->toArray();
        } else {
            $realStreamNames = $this->context->queryStreams();
        }

        $this->context->prepareStreamPositions($realStreamNames);
    }

    /**
     * @param string $streamName
     * @param \Iterator $events
     * @throws \Exception
     */
    protected function handleStreamWithSingleHandler(string $streamName, \Iterator $events): void
    {
        $this->context->setStreamName($streamName);

        $handler = $this->context->singleHandler();

        foreach ($events as $key => $event) {
            if ($this->context->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }

            $this->context->streamPositions()->set($streamName, $key);

            if ($this->isProjectorPersistent()) {
                $this->context->eventCounter()->increment();
            }

            $result = $handler($this->context->state(), $event);

            $this->context->setState($result);

            if ($this->isProjectorPersistent()) {
                $this->resetEventCounter();
            }

            if ($this->context->isStopped()) {
                break;
            }
        }
    }

    /**
     * @param string $streamName
     * @param \Iterator $events
     * @throws \Exception
     */
    protected function handleStreamWithHandlers(string $streamName, \Iterator $events): void
    {
        $this->context->setStreamName($streamName);

        $handlers = $this->context->handlers();

        foreach ($events as $key => $event) {
            if ($this->context->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }

            if ($this->isProjectorPersistent()) {
                $this->context->streamPositions()->set($streamName, $event->metadata()['_position']);
            } else {
                $this->context->streamPositions()->set($streamName, $key);
            }

            if (!isset($handlers[$event->messageName()])) {
                continue;
            }

            if ($this->isProjectorPersistent()) {
                $this->context->eventCounter()->increment();
            }

            $handler = $handlers[$event->messageName()];
            $result = $handler($this->context->state(), $event);
            $this->context->setState($result);

            if ($this->isProjectorPersistent()) {
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
        if (!$this->isProjectorPersistent()) {
            return;
        }

        if (!$this->lock->projectionExists()) {
            $this->lock->createProjection();
        }

        $this->lock->acquireLock();

        $this->prepareStreamPositions();

        $this->lock->load();
    }

    /**
     * @throws \Exception
     */
    private function resetEventCounter(): void
    {
        if (!$this->isProjectorPersistent()) {
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

    abstract protected function isProjectorPersistent(): bool;
}