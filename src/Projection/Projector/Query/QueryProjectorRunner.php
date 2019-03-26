<?php

namespace Authters\Chronicle\Projection\Projector\Query;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Projection\Factory\ProjectorRunner;
use Authters\Chronicle\Stream\StreamName;

final class QueryProjectorRunner extends ProjectorRunner
{
    /**
     * @throws \Exception
     */
    public function run(): void
    {
        $singleHandler = $this->context->hasSingleHandler();

        $this->context->stop(false);

        $this->prepareStreamPositions();

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

            $singleHandler
                ? $this->handleStreamWithSingleHandler($streamName, $streamEvents)
                : $this->handleStreamWithHandlers($streamName, $streamEvents);


            if ($this->context->isStopped()) {
                break;
            }
            if ($this->context->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }
        }
    }

    protected function isProjectorPersistent(): bool
    {
        return false;
    }
}