<?php

namespace Authters\Chronicle\Chronicler\Connection;

use Authters\Chronicle\Exceptions\ConcurrencyException;
use Authters\Chronicle\Exceptions\InvalidArgumentException;
use Authters\Chronicle\Exceptions\QueryChroniclerError;
use Authters\Chronicle\Exceptions\StreamAlreadyExists;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Connection\HasConnectionTransaction;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalChronicler;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\PersistenceStrategy;
use Authters\Chronicle\Support\Json;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Prooph\Common\Messaging\MessageFactory;

class PostgresChronicler implements TransactionalChronicler
{
    use HasConnectionTransaction;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var PersistenceStrategy
     */
    private $persistenceStrategy;

    /**
     * @var EventStreamProvider
     */
    private $eventStreamProvider;

    /**
     * @var bool
     */
    private $disableTransactionHandling;

    /**
     * @var int
     */
    private $loadBatchSize;

    /**
     * @var bool
     */
    private $duringCreate = false;

    public function __construct(Connection $connection,
                                MessageFactory $messageFactory,
                                PersistenceStrategy $persistenceStrategy,
                                EventStreamProvider $eventStreamProvider,
                                bool $disableTransactionHandling = false,
                                int $loadBatchSize = 10000)
    {
        $this->connection = $connection;
        $this->messageFactory = $messageFactory;
        $this->persistenceStrategy = $persistenceStrategy;
        $this->eventStreamProvider = $eventStreamProvider;
        $this->disableTransactionHandling = $disableTransactionHandling;
        $this->loadBatchSize = $loadBatchSize;
    }

    public function create(Stream $stream): void
    {
        $streamName = $stream->streamName();

        $this->addStreamToStreamsTable($stream);

        $tableName = $this->persistenceStrategy->generateTableName($streamName);

        try {
            $this->createSchemaFor($tableName);
        } catch (QueryChroniclerError $exception) {
            $this->connection->statement("DROP TABLE IF EXISTS `$tableName`;");
            $this->removeStreamFromEventStreamTable($streamName);

            throw $exception;
        }

        $this->appendTo($streamName, $stream->streamEvents());
    }

    public function appendTo(StreamName $streamName, \Iterator $streamEvents): void
    {
        $data = $this->persistenceStrategy->prepareData($streamEvents);

        if (empty($data)) {
            return;
        }

        $tableName = $this->persistenceStrategy->generateTableName($streamName);

        try {
            $this->connection->table($tableName)->insert($data);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '42P01') {
                throw StreamNotFound::with($streamName);
            }

            if (\in_array($exception->getCode(), ['23000', '23505'], true)) {
                throw ConcurrencyException::fromQueryErrorInfo($exception->errorInfo);
            }

            throw QueryChroniclerError::fromQueryException($exception);
        }
    }

    public function delete(StreamName $streamName): void
    {
        $this->removeStreamFromEventStreamTable($streamName);

        $tableName = $this->persistenceStrategy->generateTableName($streamName);

        try {
            $this->connection->statement("DROP TABLE IF EXISTS {$tableName}");
        } catch (QueryException $exception) {
            throw QueryChroniclerError::fromQueryException($exception);
        }
    }

    public function updateStreamMetadata(StreamName $streamName, array $newMetadata): void
    {
        try {
            $result = $this->eventStreamProvider->updateStreamMetadata(
                $streamName->toString(),
                Json::encode($newMetadata)
            );
        } catch (QueryException $exception) {
            throw QueryChroniclerError::fromQueryException($exception);
        }

        if (1 !== $result) {
            throw StreamNotFound::with($streamName);
        }
    }

    public function load(StreamName $streamName, int $fromNumber = 1, int $count = null, MetadataMatcher $metadataMatcher = null): \Iterator
    {
        return $this->loadEvents($streamName, $fromNumber, false, $count, $metadataMatcher);
    }

    public function loadReverse(StreamName $streamName, int $fromNumber = null, int $count = null, MetadataMatcher $metadataMatcher = null): \Iterator
    {
        $fromNumber = $fromNumber ?? PHP_INT_MAX;
        $orderByDesc = true;

        return $this->loadEvents($streamName, $fromNumber, $orderByDesc, $count, $metadataMatcher);
    }

    public function fetchStreamNames(?string $filter, ?MetadataMatcher $metadataMatcher, int $limit = 20, int $offset = 0): array
    {
        try {
            return $this->eventStreamProvider
                ->filterStreamNames($filter, $metadataMatcher, $limit, $offset)
                ->map(function (string $streamName) {
                    return new StreamName($streamName);
                })->toArray();
        } catch (QueryException $exception) {
            throw QueryChroniclerError::fromQueryException($exception);
        }
    }

    public function fetchCategoryNames(?string $filter, ?MetadataMatcher $metadataMatcher, int $limit = 20, int $offset = 0): array
    {
        try {
            return $this->eventStreamProvider
                ->filterCategoryNames($filter, $metadataMatcher, $limit, $offset)
                ->toArray();
        } catch (QueryException $exception) {
            throw QueryChroniclerError::fromQueryException($exception);
        }
    }

    public function hasStream(StreamName $streamName): bool
    {
        return $this->eventStreamProvider->hasRealStreamName($streamName->toString());
    }

    protected function addStreamToStreamsTable(Stream $stream): void
    {
        $realStreamName = $stream->streamName()->toString();

        $pos = \strpos($realStreamName, '-');
        $category = (false !== $pos && $pos > 0) ? \substr($realStreamName, 0, $pos) : null;

        $streamName = $this->persistenceStrategy->generateTableName($stream->streamName());
        $metadata = Json::encode($stream->metadata());

        try {
            $result = $this->eventStreamProvider->newEventStream([
                'real_stream_name' => $realStreamName,
                'stream_name' => $streamName,
                'metadata' => $metadata,
                'category' => $category
            ]);
        } catch (QueryException $exception) {
            if (\in_array($exception->getCode(), ['23000', '235050'], true)) {
                throw StreamAlreadyExists::with($stream->streamName());
            }

            throw QueryChroniclerError::fromQueryException($exception);
        }

        if (!$result) {
            throw new QueryChroniclerError("Unable to insert data in event stream table");
        }
    }

    protected function removeStreamFromEventStreamTable(StreamName $streamName): void
    {
        $result = null;
        try {
            $result = $this->eventStreamProvider->deleteRealStreamName($streamName->toString());
        } catch (QueryException $exception) {
            // checkMe
            if($exception->getCode() !== '00000'){
                throw QueryChroniclerError::fromQueryException($exception);
            }
        }

        if (1 !== $result) {
            throw StreamNotFound::with($streamName);
        }
    }

    protected function createSchemaFor(string $tableName): void
    {
        $schema = $this->persistenceStrategy->up($tableName);

        try {
            $this->connection->getSchemaBuilder()->create($tableName, $schema);
        } catch (QueryException $exception) {
            throw QueryChroniclerError::fromQueryException($exception);
        }
    }

    protected function loadEvents(StreamName $streamName,
                                  int $fromNumber,
                                  bool $orderByDesc,
                                  int $count = null,
                                  MetadataMatcher $metadataMatcher = null): \Iterator
    {
        $tableName = $this->persistenceStrategy->generateTableName($streamName);

        if (!$this->eventStreamProvider->hasRealStreamName($streamName)) {
            throw StreamNotFound::with($streamName);
        }

        $builder = $this->connection->table($tableName);

        $operator = $orderByDesc ? '<=' : '>=';
        $builder->where('no', $operator, $fromNumber);

        if ($metadataMatcher) {
            $data = $metadataMatcher->data();

            if (!is_callable($data)) {
                throw new InvalidArgumentException("Metadata matcher must return a callable");
            }

            $data($builder);
        }

        $orderBy = $orderByDesc ? 'DESC' : 'ASC';
        $builder->orderBy('no', $orderBy);

        $limit = null === $count ? $this->loadBatchSize : \min($count, $this->loadBatchSize);

        try {
            $result = (new QueryStreamBuilder($this->messageFactory, $builder, $limit))->chunk();
        } catch (QueryException $exception) {
            if ($exception->getCode() === '42703') {
                throw QueryChroniclerError::fromQueryException($exception);
            }

            if ($exception->getCode() !== '00000') {
                throw StreamNotFound::with($streamName);
            }

            throw QueryChroniclerError::fromQueryException($exception);
        }

        if (0 === iterator_count($result)) {
            throw StreamNotFound::with($streamName);
        }

        return $result;
    }

    protected function isTransactionDisabled(): bool
    {
        return $this->disableTransactionHandling;
    }

    protected function connection(): Connection
    {
        return $this->connection;
    }
}