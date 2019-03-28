<?php

namespace Authters\Chronicle\Aggregate;

use Authters\Chronicle\Aggregate\Model\AggregateModelRoot;
use Authters\Chronicle\Exceptions\RuntimeException;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcherAggregate;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Chronicle\Support\Contracts\Projection\Strategy\StreamNamingStrategy;
use Prooph\Common\Messaging\Message;

abstract class AbstractRepository
{
    /**
     * @var Chronicler
     */
    protected $chronicler;

    /**
     * @var AggregateType
     */
    protected $aggregateType;

    /**
     * @var StreamNamingStrategy
     */
    protected $namingStrategy;

    /**
     * @var MetadataMatcherAggregate
     */
    protected $metadataMatchers;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $identityMap = [];

    public function __construct(Chronicler $chronicler,
                                AggregateType $aggregateType,
                                StreamNamingStrategy $namingStrategy,
                                MetadataMatcherAggregate $metadataMatchers,
                                array $metadata = [])
    {
        $this->chronicler = $chronicler;
        $this->aggregateType = $aggregateType;
        $this->namingStrategy = $namingStrategy;
        $this->metadataMatchers = $metadataMatchers;
        $this->metadata = $metadata;
    }

    /**
     * @param object $root
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function saveAggregateRoot(object $root): void
    {
        $this->assertAggregateRootType($root);
        $this->assertAggregateType($root);

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

            $eventStreams = $this->chronicler->load(
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

        /** @var AggregateRoot|AggregateModelRoot $aggregateType */
        $aggregateType = $this->aggregateType->toString();

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

            $this->chronicler->create($stream);
        } else {
            $this->chronicler->appendTo($streamName, $enrichedEvents);
        }
    }

    protected function enrichEventMetadata(Message $domainEvent, string $aggregateId): Message
    {
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_id', $aggregateId);
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_type', $this->aggregateType->toString());

        return $domainEvent;
    }

    protected function determineStreamName(string $aggregateId): StreamName
    {
        return $this->namingStrategy->determineStreamName($aggregateId, $this->aggregateType->toString());
    }

    protected function metadataMatcherFromPersistenceStrategy(string $aggregateId): ?MetadataMatcher
    {
        if ($this->namingStrategy->isOneStreamPerAggregate()) {
            return null;
        }

        return $this->metadataMatchers->matchAggregateIdAndType($aggregateId, $this->aggregateType->toString());
    }

    protected function isFirstEvent(Message $message): bool
    {
        return 1 === $message->metadata()['_aggregate_version'];
    }

    protected function assertAggregateType(object $root): void
    {
        $this->aggregateType->assert($root);
    }

    /**
     * @param object $root
     * @return string
     * @throws \ReflectionException
     */
    protected function getAggregateId(object $root): string
    {
        $this->assertAggregateRootType($root);

        $class = new \ReflectionMethod($root, 'aggregateId');
        $class->setAccessible(true);

        return $class->invoke($root);
    }

    protected function assertAggregateRootType(object $root): void
    {
        if (!$root instanceof AggregateRoot && !$root instanceof AggregateModelRoot) {
            throw new RuntimeException("Object root is invalid");
        }
    }
}