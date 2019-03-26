<?php

namespace Authters\Chronicle\Projection\Projector\Query;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Projection\Factory\ProjectorRunner;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

class QueryProjectorRunner extends ProjectorRunner
{
    public function __construct(QueryProjectorContext $context, ProjectorConnector $connector)
    {
        $this->connector = $connector;
        $this->context = $context;
    }

    public function run(): void
    {
        $singleHandler = $this->context->hasSingleHandler();

        $this->context->stop(false);

        $this->prepareStreamPositions();

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
            if ($this->context->options()->triggerPcntlSignalDispatch) {
                \pcntl_signal_dispatch();
            }
        }
    }

    protected function isPersistent(): bool
    {
        return false;
    }
}