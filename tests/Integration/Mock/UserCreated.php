<?php

namespace AuthtersTest\Chronicle\Integration\Mock;

use Prooph\Common\Messaging\DomainEvent;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\PayloadTrait;

class UserCreated extends DomainEvent
{
    use PayloadTrait;

    public static function with(array $payload, int $version): Message
    {
        $event = new static($payload);

        return $event->withVersion($version);
    }

    public function withVersion(int $version): Message
    {
        return $this->withAddedMetadata('_aggregate_version', $version);
    }

    public function version(): int
    {
        return $this->metadata['_aggregate_version'];
    }
}