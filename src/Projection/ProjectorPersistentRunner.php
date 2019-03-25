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

        $singleHandler = $this->context->hasSingleHandler();
        $this->context->stop(false);

        try {
            do {
                foreach ($this->context->streamPositions()->all() as $streamName => $position) {
                    try {
                        $streamEvents = $this->connector->publisher()->load(
                            new StreamName($streamName),
                            $position + 1,
                            null,
                            $this->context->metadataMatcher()
                        );

                    } catch (StreamNotFound $e) {
                        continue;
                    }

                    if ($singleHandler) {
                        $this->handleStreamWithSingleHandler($streamName, $streamEvents);
                    } else {
                        $this->handleStreamWithHandlers($streamName, $streamEvents);
                    }

                    if ($this->context->isStopped()) {
                        break;
                    }
                }

                $this->handleEventCounter();

                if ($this->context->options()->triggerPcntlSignalDispatch) {
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
            } while ($keepRunning && !$this->context->isStopped());
        } finally {
            $this->lock->releaseLock();
        }
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

            $this->context->eventCounter()->increment();

            $result = $handler($this->context->state(), $event);

            $this->context->setState($result);

            $this->resetEventCounter();

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

            if( $event instanceof Message){
                $this->context->streamPositions()->set($streamName, $event->metadata()['_position']);
            }else{
                $this->context->streamPositions()->set($streamName, $key);
            }

            if (!isset($handlers[$event->messageName()])) {
                continue;
            }

            $this->context->eventCounter()->increment();

            $handler = $handlers[$event->messageName()];
            $result = $handler($this->context->state(), $event);
            $this->context->setState($result);

            $this->resetEventCounter();

            if ($this->context->isStopped()) {
                break;
            }
        }
    }

    protected function resetEventCounter(): void
    {
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
        if ($this->context->eventCounter()->isReset()) {
            \usleep($this->context->options()->sleep);
            $this->lock->updateLock();
        } else {
            $this->lock->persist();
        }

        $this->context->eventCounter()->reset();
    }
}