<?php

namespace Authters\Chronicle\Aggregate\Concerns;

use Authters\Chronicle\Aggregate\AggregateChanged;

trait HasEventProducer
{
    /**
     * @var int
     */
    protected $version = 0;

    /**
     * @var array
     */
    protected $recordedEvents = [];

    public function popRecordedEvents(): array
    {
        $pendingEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $pendingEvents;
    }

    protected function recordThat(AggregateChanged $event): void
    {
        $this->version += 1;

        $this->recordedEvents[] = $event->withVersion($this->version);

        $this->apply($event);
    }
}