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
        if ($this->stopRunnerProcessing(true, $keepRunning)) {
            return;
        }

        $this->prepareExecution();

        $singleHandler = $this->context->hasSingleHandler();
        $this->context->stop(false);

        try {
            do {
                foreach ($this->context->streamPositions()->all() as $streamName => $position) {
                    try {
                        $streamEvents = $this->chronicler->load(
                            new StreamName($streamName),
                            $position + 1,
                            null,
                            $this->context->metadataMatcher()
                        );
                    } catch (StreamNotFound $e) {
                        continue;
                    }

                    $singleHandler
                        ? $this->handleStreamWithSingleHandler($streamName, $streamEvents)
                        : $this->handleStreamWithHandlers($streamName, $streamEvents);

                    if ($this->context->isStopped()) {
                        break;
                    }
                }

                $this->handleEventCounter();

                if ($this->context->options()->triggerPcntlSignalDispatch) {
                    \pcntl_signal_dispatch();
                }

                $this->stopRunnerProcessing(false, $keepRunning);

                $this->prepareStreamPositions();
            } while ($keepRunning && !$this->context->isStopped());
        } finally {
            $this->lock->releaseLock();
        }
    }

    /**
     * @param bool $firstExecution
     * @param bool $keepRunning
     * @return bool
     * @throws \Exception
     */
    private function stopRunnerProcessing(bool $firstExecution, bool $keepRunning): bool
    {
        switch ($this->lock->fetchRemoteStatus()) {
            case ProjectionStatus::STOPPING():
                if ($firstExecution) {
                    $this->lock->load();
                }

                $this->lock->stop();

                return $firstExecution;
            case ProjectionStatus::DELETING():
                $this->lock->delete(false);

                return $firstExecution;
            case ProjectionStatus::DELETING_INCL_EMITTED_EVENTS():
                $this->lock->delete(true);

                return $firstExecution;
            case ProjectionStatus::RESETTING():
                $this->lock->reset();

                if (!$firstExecution && $keepRunning) {
                    $this->lock->startAgain();
                }

                return false;
            default:
                return false;
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