<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjector;

class ReadModelHandlerContext
{
    /**
     * @var ReadModelProjector
     */
    private $projector;

    /**
     * @var string|null
     */
    private $streamName;

    public function __construct(ReadModelProjector $projector, ?string &$streamName)
    {
        $this->projector = $projector;
        $this->streamName = $streamName;
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

    public function __invoke(?string $streamName): self
    {
        return new self($this->projector, $streamName);
    }
}