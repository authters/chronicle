<?php

namespace Authters\Chronicle\Aggregate;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcherAggregate;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\StreamNamingStrategy;
use Prooph\Common\Messaging\Message;

class AggregateRepository
{
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var AggregateType
     */
    private $modelType;

    /**
     * @var StreamNamingStrategy
     */
    private $namingStrategy;

    /**
     * @var MetadataMatcherAggregate
     */
    private $metadataMatchers;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $identityMap = [];

    public function __construct(Publisher $publisher,
                                AggregateType $modelType,
                                StreamNamingStrategy $namingStrategy,
                                MetadataMatcherAggregate $metadataMatchers,
                                array $metadata = [])
    {
        $this->publisher = $publisher;
        $this->modelType = $modelType;
        $this->namingStrategy = $namingStrategy;
        $this->metadataMatchers = $metadataMatchers;
        $this->metadata = $metadata;
    }

    /**
     * @param AggregateRoot $root
     * @throws \Throwable
     */
    protected function saveAggregateRoot(AggregateRoot $root): void
    {
        $this->assertModelType($root);

        $domainEvents = $root->popRecordedEvents();

        $firstEvent = \reset($domainEvents);
        if (false === $firstEvent) {
            return;
        }

        $aggregateId = $this->getAggregateId($root);

        $enrichedEvents = [];
        foreach ($domainEvents as $event) {
            $enrichedEvents [] = $this->enrichEventMetadata($event, $aggregateId);
        }

        $this->createOrAppend($firstEvent, new \ArrayIterator($enrichedEvents), $aggregateId);

        if (isset($this->identityMap[$aggregateId])) {
            unset($this->identityMap[$aggregateId]);
        }
    }

    public function getAggregateRoot(string $aggregateId): ?object
    {
        $streamName = $this->determineStreamName($aggregateId);

        if (isset($this->identityMap[$aggregateId])) {
            return $this->identityMap[$aggregateId];
        }

        try {
            $metadataMatcher = $this->metadataMatcherFromPersistenceStrategy($aggregateId);

            $eventStreams = $this->publisher->load(
                $streamName,
                1,
                $count = null,
                $metadataMatcher
            );
        } catch (StreamNotFound $streamNotFound) {
            return null;
        }

        if (0 === iterator_count($eventStreams)) {
            return null;
        }

        /** @var AggregateRoot $aggregateType */
        $aggregateType = $this->modelType->toString();

        return $this->identityMap[$aggregateId] = $aggregateType::reconstituteFromHistory($eventStreams);
    }

    /**
     * @param Message $firstEvent
     * @param \Iterator $enrichedEvents
     * @param string $aggregateId
     * @throws \Throwable
     */
    protected function createOrAppend(Message $firstEvent, \Iterator $enrichedEvents, string $aggregateId): void
    {
        $streamName = $this->determineStreamName($aggregateId);

        if ($this->namingStrategy->isOneStreamPerAggregate() && $this->isFirstEvent($firstEvent)) {
            $stream = new Stream(new StreamName($streamName), $enrichedEvents, $this->metadata);

            $this->publisher->create($stream);
        } else {
            $this->publisher->appendTo($streamName, $enrichedEvents);
        }
    }

    protected function enrichEventMetadata(Message $domainEvent, string $aggregateId): Message
    {
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_id', $aggregateId);
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_type', $this->modelType->toString());

        return $domainEvent;
    }

    protected function determineStreamName(string $aggregateId): StreamName
    {
        return $this->namingStrategy->determineStreamName($aggregateId, $this->modelType->toString());
    }

    protected function metadataMatcherFromPersistenceStrategy(string $aggregateId): ?MetadataMatcher
    {
        if ($this->namingStrategy->isOneStreamPerAggregate()) {
            return null;
        }

        return $this->metadataMatchers->matchAggregateIdAndType($aggregateId, $this->modelType->toString());
    }

    protected function isFirstEvent(Message $message): bool
    {
        return 1 === $message->metadata()['_aggregate_version'];
    }

    /**
     * @param AggregateRoot $root
     * @return string
     * @throws \ReflectionException
     */
    protected function getAggregateId(AggregateRoot $root): string
    {
        $class = new \ReflectionMethod($root, 'aggregateId');
        $class->setAccessible(true);

        return $class->invoke($root);
    }

    protected function assertModelType(object $model): void
    {
        $this->modelType->assert($model);
    }
}