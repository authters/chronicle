<?php

namespace Authters\Chronicle\Projection\Strategy;

use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\PersistenceStrategy;
use Authters\Chronicle\Support\Json;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageConverter;
use Prooph\Common\Messaging\NoOpMessageConverter;

class MysqlSingleStreamStrategy implements PersistenceStrategy
{
    /**
     * @var MessageConverter
     */
    private $messageConverter;

    public function __construct(?MessageConverter $messageConverter = null)
    {
        $this->messageConverter = $messageConverter ?? new NoOpMessageConverter();
    }

    public function up(string $tableName): callable
    {
        return function (Blueprint $table) {
            $table->collation = 'utf8mb4_bin';
            $table->charset = 'utf8mb4';

            $table->bigInteger('no', true);
            $table->uuid('event_id');
            $table->string('event_name', 100);
            $table->json('metadata');
            $table->json('payload');
            $table->dateTime('created_at', 6);
            $table->integer('aggregate_version', false, 11)->storedAs(
                'JSON_UNQUOTE(JSON_EXTRACT(metadata, \'$._aggregate_version\'))'
            );
            $table->uuid('aggregate_id')->storedAs(
                'JSON_UNQUOTE(JSON_EXTRACT(metadata, \'$._aggregate_id\'))'
            );
            $table->string('aggregate_type', 150)->storedAs(
                'JSON_UNQUOTE(JSON_EXTRACT(metadata, \'$._aggregate_type\'))'
            );

            $table->unique('event_id', 'ix_event_id');
            $table->unique(['aggregate_id', 'aggregate_version', 'aggregate_type'], 'ix_unique_event');
            $table->index(['aggregate_type', 'aggregate_id', 'no'], 'ix_query_aggregate');
        };
    }

    public function columnNames(): array
    {
        return [
            'event_id',
            'event_name',
            'payload',
            'metadata',
            'created_at',
        ];
    }

    public function prepareData(\Iterator $streamEvents): array
    {
        $eventCollection = new Collection($streamEvents);

        if ($eventCollection->isEmpty()) {
            return [];
        }

        return $eventCollection->transform(function (Message $event) {
            $data = $this->messageConverter->convertToArray($event);

            return array_combine($this->columnNames(), [
                'uuid' => $data['uuid'],
                'message_name' => $data['message_name'],
                'payload' => Json::encode($data['payload']),
                'metadata' => Json::encode($data['metadata']),
                'created_at' => $this->formatDateTime($data['created_at'])
            ]);
        })->toArray();
    }

    public function generateTableName(StreamName $streamName): string
    {
        return '_' . \sha1($streamName->toString());
    }

    public function indexName(): string
    {
        return 'ix_query_aggregate';
    }

    protected function formatDateTime(\DateTimeImmutable $createdAt): string
    {
        return $createdAt->format('Y-m-d\TH:i:s.u');
    }
}