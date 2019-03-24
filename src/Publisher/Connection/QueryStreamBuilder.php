<?php

namespace Authters\Chronicle\Publisher\Connection;

use Authters\Chronicle\Support\Json;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;

class QueryStreamBuilder
{
    /**
     * @var Collection
     */
    private $events;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var int
     */
    private $limit;

    public function __construct(MessageFactory $messageFactory, Builder $builder, int $limit)
    {
        $this->messageFactory = $messageFactory;
        $this->builder = $builder;
        $this->limit = $limit;
        $this->events = new Collection();
    }

    public function chunk(): Collection
    {
        $this->builder->chunk($this->limit, function (Collection $events) {
            $events->each(function (\stdClass $event) {
                $this->events->push($this->toMessage($event));
            });
        });

        return $this->events;
    }

    protected function toMessage(\stdClass $event): Message
    {
        $createdAt = $this->formatDate($event->created_at);

        $payload = Json::decode($event->payload);

        $metadata = Json::decode($event->metadata);
        if (!\array_key_exists('_position', $metadata)) {
            $metadata['_position'] = $event->no;
        }

        return $this->messageFactory->createMessageFromArray($event->event_name, [
            'uuid' => $event->event_id,
            'created_at' => $createdAt,
            'payload' => $payload,
            'metadata' => $metadata,
        ]);
    }

    protected function formatDate(string $createdAt): \DateTimeImmutable
    {
        if (\strlen($createdAt) === 19) {
            $createdAt = $createdAt . '.000';
        }

        return \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s.u',
            $createdAt,
            new \DateTimeZone('UTC')
        );
    }
}