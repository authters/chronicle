<?php

namespace Authters\Chronicle\Projection;


use Authters\Chronicle\Support\Projection\EventCounter;
use Authters\Chronicle\Support\Projection\StreamPositions;

class ProjectorMutable
{
    /**
     * @var EventCounter
     */
    protected $eventCounter;

    /**
     * @var StreamPositions
     */
    protected $streamPositions;

    /**
     * @var ProjectionStatus
     */
    protected $status;

    /**
     * @var array
     */
    protected $state;

    /**
     * @var boolean
     */
    protected $isStopped;

    /**
     * @var ?string
     */
    protected $currentStreamName;

    /**
     * @var bool
     */
    protected $streamCreated = false;

    public function __construct()
    {
        $this->streamPositions = new StreamPositions();
        $this->eventCounter = new EventCounter();
        $this->status = ProjectionStatus::IDLE();

        $this->streamCreated = false;
        $this->isStopped = false;
        $this->currentStreamName = null;
        $this->state = [];
    }

    public function stop(bool $stop): void
    {
        $this->isStopped = $stop;
    }

    public function isStopped(): bool
    {
        return $this->isStopped;
    }

    public function eventCounter(): EventCounter
    {
        return $this->eventCounter;
    }

    public function prepareStreamPositions(iterable $names)
    {
        $streamPositions = [];
        foreach ($names as $name) {
            $streamPositions[$name] = 0;
        }

        $this->streamPositions->merge($streamPositions);
    }

    public function streamPositions(): StreamPositions
    {
        return $this->streamPositions;
    }

    public function setStatus(ProjectionStatus $status): void
    {
        $this->status = $status;
    }

    public function status(): ProjectionStatus
    {
        return $this->status;
    }

    public function resetState(): void
    {
        $this->state = [];
    }

    public function setState($result = null): void
    {
        if (\is_array($result)) {
            $this->state = $result;
        }
    }

    public function state(): array
    {
        return $this->state;
    }

    public function currentStreamName(): ?string
    {
        return $this->currentStreamName;
    }

    public function setStreamName(string $streamName = null): void
    {
        $this->currentStreamName = $streamName;
    }
}