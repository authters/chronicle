<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorContext;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\Projector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjector as BaseReadModelProjector;

class ReadModelProjectorContext extends ProjectorContext
{
    protected function createHandlerContext(Projector $projector, ?string &$streamName): object
    {
        return new class($projector, $streamName)
        {
            /**
             * @var ReadModelProjector
             */
            private $projector;

            /**
             * @var ?string
             */
            private $streamName;

            public function __construct(BaseReadModelProjector $projector, ?string &$streamName)
            {
                $this->projector = $projector;
                $this->streamName = &$streamName;
            }

            public function stop(): void
            {
                $this->projector->stop();
            }

            public function readModel(): ReadModel
            {
                return $this->projector->readModel();
            }

            public function streamName(): ?string
            {
                return $this->streamName;
            }
        };
    }
}