<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\StreamName;
use Prooph\Common\Messaging\Message;

abstract class ProjectorPersistentRunner extends ProjectorRunner
{
    /**
     * @param bool $keepRunning
     * @throws \Exception
     */
    public function run(bool $keepRunning = true): void
    {
        switch ($this->lock->fetchRemoteStatus()) {
            case ProjectionStatus::STOPPING():
                $this->lock->load();
                $this->lock->stop();

                return;
            case ProjectionStatus::DELETING():
                $this->lock->delete(false);

                return;
            case ProjectionStatus::DELETING_INCL_EMITTED_EVENTS():
                $this->lock->delete(true);

                return;
            case ProjectionStatus::RESETTING():
                $this->lock->reset();
                break;

            default:
                break;
        }

        $this->prepareExecution();

        $singleHandler = $this->builder->hasSingleHandler();
        $this->mutable->stop(false);

        try {
            do {
                foreach ($this->mutable->streamPositions() as $streamName => $position) {
                    try {

                        $streamEvents = $this->connector->publisher()->load(
                            new StreamName($streamName),
                            $position + 1,
                            null,
                            $this->builder->metadataMatcher()
                        );

                    } catch (StreamNotFound $e) {
                        continue;
                    }

                    if ($singleHandler) {
                        $this->handleStreamWithSingleHandler($streamName, $streamEvents);
                    } else {
                        $this->handleStreamWithHandlers($streamName, $streamEvents);
                    }

                    if ($this->mutable->isStopped()) {
                        break;
                    }
                }

                $this->handleEventCounter();

                if ($this->builder->options()->triggerPcntlSignalDispatch) {
                    \pcntl_signal_dispatch();
                }

                switch ($this->lock->fetchRemoteStatus()) {
                    case ProjectionStatus::STOPPING():
                        $this->lock->stop();

                        break;
                    case ProjectionStatus::DELETING():
                        $this->lock->delete(false);

                        break;
                    case ProjectionStatus::DELETING_INCL_EMITTED_EVENTS():
                        $this->lock->delete(false);

                        break;
                    case ProjectionStatus::RESETTING():
                        $this->lock->reset();
                        if ($keepRunning) {
                            $this->lock->startAgain();
                        }
                        break;

                    default:
                        break;
                }

                $this->prepareStreamPositions();
            } while ($keepRunning && !$this->mutable->isStopped());
        } finally {
            $this->lock->releaseLock();
        }
    }

    protected function handleStreamWithSingleHandler(string $streamName, \Iterator $events): void
    {
        $this->mutable->setStreamName($streamName);

        $handler = $this->builder->singleHandler();

        foreach ($events as $key => $event) {
            if ($this->builder->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }

            $this->mutable->streamPositions()->set($streamName, $key);

            $this->mutable->eventCounter()->increment();

            $result = $handler($this->mutable->state(), $event);

            $this->mutable->setState($result);

            $this->resetEventCounter();

            if ($this->mutable->isStopped()) {
                break;
            }
        }
    }

    protected function handleStreamWithHandlers(string $streamName, \Iterator $events): void
    {
        $this->mutable->setStreamName($streamName);

        $handlers = $this->builder->handlers();

        /* @var Message $event */
        foreach ($events as $key => $event) {
            if ($this->builder->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }

            if( $event instanceof Message){
                $this->mutable->streamPositions()->set($streamName, $event->metadata()['_position']);
            }else{
                $this->mutable->streamPositions()->set($streamName, $key);
            }

            if (!isset($handlers[$event->messageName()])) {
                continue;
            }

            $this->mutable->eventCounter()->increment();

            $handler = $handlers[$event->messageName()];
            $result = $handler($this->mutable->state(), $event);
            $this->mutable->setState($result);

            $this->resetEventCounter();

            if ($this->mutable->isStopped()) {
                break;
            }
        }
    }

    protected function resetEventCounter(): void
    {
        if ($this->mutable->eventCounter()->isEqualsTo($this->builder->options()->persistBlockSize)) {
            $this->lock->persist();

            $this->mutable->eventCounter()->reset();

            $this->mutable->setStatus(
                $this->lock->fetchRemoteStatus()
            );

            if (!$this->mutable->status()->is(ProjectionStatus::RUNNING())
                && !$this->mutable->status()->is(ProjectionStatus::IDLE())) {
                $this->mutable->stop(true);
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function prepareExecution(): void
    {
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
    protected function handleEventCounter(): void
    {
        if ($this->mutable->eventCounter()->isEqualsTo(0)) {
            \usleep($this->builder->options()->sleep);
            $this->lock->updateLock();
        } else {
            $this->lock->persist();
        }

        $this->mutable->eventCounter()->reset();
    }
}