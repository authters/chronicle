<?php

namespace Authters\Chronicle\Metadata\Causation;

use Authters\Chronicle\Support\Contracts\Metadata\MetadataEnricher;
use Prooph\Common\Messaging\Message;

class CausationMetadataEnricher implements MetadataEnricher
{
    /**
     * @var Message
     */
    private $command;

    public function __construct(Message $command)
    {
        $this->command = $command;
    }

    public function enrich(Message $message): Message
    {
        $message = $message->withAddedMetadata(
            $this->causationIdKey(),
            $this->getCommand()->uuid()->toString()
        );

        $message = $message->withAddedMetadata(
            $this->causationNameKey(),
            $this->getCommand()->messageName()
        );

        return $message;
    }

    public function reset(): void
    {
        $this->command = null;
    }

    public function getCommand(): Message
    {
        return $this->command;
    }

    public function causationIdKey(): string
    {
        return '_causation_id';
    }

    public function causationNameKey(): string
    {
        return '_causation_name';
    }
}