<?php

namespace Authters\Chronicle\Aggregate;

use Assert\Assertion;
use Prooph\Common\Messaging\DomainEvent;

class AggregateChanged extends DomainEvent
{
    /**
     * @var array
     */
    protected $payload = [];

    public static function occur(string $aggregateId, array $payload = []): self
    {
        return new static($aggregateId, $payload);
    }

    protected function __construct(string $aggregateId, array $payload, array $metadata = [])
    {
        $this->metadata = $metadata;
        $this->setAggregateId($aggregateId);
        $this->setVersion($metadata['_aggregate_version'] ?? 1);
        $this->setPayload($payload);
        $this->init();
    }

    public function aggregateId(): string
    {
        return $this->metadata['_aggregate_id'];
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function version(): int
    {
        return $this->metadata['_aggregate_version'];
    }

    public function withVersion(int $version): AggregateChanged
    {
        $self = clone $this;
        $self->setVersion($version);

        return $self;
    }

    protected function setAggregateId(string $aggregateId): void
    {
        Assertion::notEmpty($aggregateId);

        $this->metadata['_aggregate_id'] = $aggregateId;
    }

    protected function setVersion(int $version): void
    {
        $this->metadata['_aggregate_version'] = $version;
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}