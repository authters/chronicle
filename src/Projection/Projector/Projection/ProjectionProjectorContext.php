<?php

namespace Authters\Chronicle\Projection\Projector\Projection;

use Authters\Chronicle\Projection\Factory\PersistentProjectorContext;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectionProjector as BaseProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\Projector;
use Authters\Chronicle\Support\Projection\ArrayCache;
use Prooph\Common\Messaging\Message;

class ProjectionProjectorContext extends PersistentProjectorContext
{
    /**
     * @var ArrayCache
     */
    private $cachedStreamNames;

    /**
     * @var ProjectionProjectorOptions
     */
    protected $options;

    public function __construct(EventStreamProvider $eventStreamProvider, ProjectionProjectorOptions $options)
    {
        parent::__construct($eventStreamProvider, $options);

        $this->cachedStreamNames = new ArrayCache($options->cacheSize);
    }

    public function cachedStreamNames(): ArrayCache
    {
        return $this->cachedStreamNames;
    }

    protected function createHandlerContext(Projector $projector, ?string &$streamName): object
    {
        return new class($projector, $streamName)
        {
            /**
             * @var BaseProjector
             */
            private $projector;

            /**
             * @var ?string
             */
            private $streamName;

            public function __construct(BaseProjector $projector, ?string &$streamName)
            {
                $this->projector = $projector;
                $this->streamName = &$streamName;
            }

            public function stop(): void
            {
                $this->projector->stop();
            }

            public function linkTo(string $streamName, Message $event): void
            {
                $this->projector->linkTo($streamName, $event);
            }

            public function emit(Message $event): void
            {
                $this->projector->emit($event);
            }

            public function streamName(): ?string
            {
                return $this->streamName;
            }
        };
    }
}