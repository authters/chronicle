<?php

namespace Authters\Chronicle\Projection\Projector\Query;

use Authters\Chronicle\Projection\Factory\ProjectorContext;
use Authters\Chronicle\Support\Contracts\Projection\Projector\Projector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector as BaseProjector;

class QueryProjectorContext extends ProjectorContext
{
    protected function createHandlerContext(Projector $projector, ?string &$streamName): object
    {
        return new class($this, $streamName)
        {
            /**
             * @var BaseProjector
             */
            private $query;

            /**
             * @var ?string
             */
            private $streamName;

            public function __construct(BaseProjector $query, ?string &$streamName)
            {
                $this->query = $query;
                $this->streamName = &$streamName;
            }

            public function stop(): void
            {
                $this->query->stop();
            }

            public function streamName(): ?string
            {
                return $this->streamName;
            }
        };
    }
}