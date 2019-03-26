<?php

namespace Authters\Chronicle\Projection\Factory;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\StreamName;

abstract class PersistentProjectorRunner extends ProjectorRunner
{
    /**
     * @var PersistentProjectorContext
     */
    protected $context;

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
                        $streamEvents = $this->publisher->load(
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

    /**
     * @throws \Exception
     */
    private function handleEventCounter(): void
    {
        if ($this->context->eventCounter()->isReset()) {
            \usleep($this->context->options()->sleep);
            $this->lock->updateLock();
        } else {
            $this->lock->persist();
        }

        $this->context->eventCounter()->reset();
    }

    protected function isProjectorPersistent(): bool
    {
        return true;
    }
}