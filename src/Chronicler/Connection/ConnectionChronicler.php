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
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalChronicler;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\PersistenceStrategy;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\QueryHint;
use Authters\Chronicle\Support\Json;
use Authters\Chronicle\Support\Projection\InternalProjectionName;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Prooph\Common\Messaging\MessageFactory;

class ConnectionChronicler implements TransactionalChronicler
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

        if (!$this->disableTransactionHandling) {
            $this->connection->beginTransaction();
            $this->duringCreate = true;
        }

        try {
            $this->appendTo($streamName, $stream->streamEvents());
        } catch (\Throwable $exception) {
            if (!$this->disableTransactionHandling) {
                $this->connection->rollBack();
                $this->duringCreate = false;
            }

            throw $exception;
        }

        if (!$this->disableTransactionHandling) {
            $this->connection->commit();
            $this->duringCreate = false;
        }
    }

    public function appendTo(StreamName $streamName, \Iterator $streamEvents): void
    {
        $data = $this->persistenceStrategy->prepareData($streamEvents);

        if (empty($data)) {
            return;
        }

        $tableName = $this->persistenceStrategy->generateTableName($streamName);

        if (!$this->inTransaction()) {
            $this->connection->beginTransaction();
        }

        try {
            $this->connection->table($tableName)->insert($data);
        } catch (QueryException $exception) {
            if ($this->inTransaction() && !$this->duringCreate) {
                $this->connection->rollBack();
            }

            if ($exception->getCode() === '42S02') {
                throw StreamNotFound::with($streamName);
            }

            if ($exception->getCode() === '23000') {
                throw ConcurrencyException::fromQueryErrorInfo($exception->errorInfo);
            }

            throw QueryChroniclerError::fromQueryException($exception);
        }

        if ($this->inTransaction() && !$this->duringCreate) {
            $this->connection->commit();
        }
    }

    public function delete(StreamName $streamName): void
    {
        if (!$this->inTransaction()) {
            $this->connection->beginTransaction();
        }

        try {
            $this->removeStreamFromEventStreamTable($streamName);
        } catch (StreamNotFound $exception) {
            if ($this->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }

        $tableName = $this->persistenceStrategy->generateTableName($streamName);

        try {
            $this->connection->statement("DROP TABLE IF EXISTS {$tableName}");
        } catch (QueryException $exception) {
            throw QueryChroniclerError::fromQueryException($exception);
        }

        if ($this->inTransaction()) {
            $this->connection->commit();
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
            throw  StreamNotFound::with($streamName);
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
                ->filterCategoryNames($filter, $metadataMatcher, $limit, $offset)->toArray();
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
        $category = InternalProjectionName::fromCategory($stream->streamName());
        $categoryName = $category->isValid() ? $category->toString() : null;

        $streamName = $this->persistenceStrategy->generateTableName($stream->streamName());
        $metadata = Json::encode($stream->metadata());

        try {
            $result = $this->eventStreamProvider->newEventStream([
                'real_stream_name' => $realStreamName,
                'stream_name' => $streamName,
                'metadata' => $metadata,
                'category' => $categoryName
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
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
        try {
            $result = $this->eventStreamProvider->deleteRealStreamName($streamName->toString());
        } catch (QueryException $exception) {
            throw QueryChroniclerError::fromQueryException($exception);
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

        $queryHint = null;
        if ($this->persistenceStrategy instanceof QueryHint) {
            $index = $this->persistenceStrategy->indexName();
            $queryHint = " USE INDEX($index)";
        }

        $builder = $this->connection->table(
            $this->connection->raw($tableName . $queryHint)
        );

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
            throw QueryChroniclerError::fromQueryException($exception);
        }

        //fixMe
        if (!$result || 0 === iterator_count($result)) {
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