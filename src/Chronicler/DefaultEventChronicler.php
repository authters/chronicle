<?php

namespace Authters\Chronicle\Chronicler;

use Authters\Chronicle\Exceptions\InvalidArgumentException;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Chronicler\Events\AppendToEvent;
use Authters\Chronicle\Chronicler\Events\CreateEvent;
use Authters\Chronicle\Chronicler\Events\DeleteEvent;
use Authters\Chronicle\Chronicler\Events\FetchCategoryNamesEvent;
use Authters\Chronicle\Chronicler\Events\FetchStreamNamesEvent;
use Authters\Chronicle\Chronicler\Events\HasStreamEvent;
use Authters\Chronicle\Chronicler\Events\LoadEvent;
use Authters\Chronicle\Chronicler\Events\LoadReverseEvent;
use Authters\Chronicle\Chronicler\Events\UpdateStreamMetadataEvent;
use Authters\Chronicle\Chronicler\Tracker\EventTracker;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\EventChronicler;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;

class DefaultEventChronicler implements EventChronicler
{
    /**
     * @var Chronicler
     */
    protected $publisher;

    /**
     * @var EventTracker
     */
    protected $tracker;

    public function __construct(Chronicler $publisher, EventTracker $tracker)
    {
        $this->publisher = $publisher;
        $this->tracker = $tracker;
    }

    public function create(Stream $stream): void
    {
        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new CreateEvent($this->publisher));

        $event->setStream($stream);

        $this->tracker->emit($event);

        if ($exception = $event->streamAlreadyExists()) {
            throw $exception;
        }
    }

    public function appendTo(StreamName $streamName, \Iterator $streamEvents): void
    {
        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new AppendToEvent($this->publisher));
        $event->setStreamName($streamName);
        $event->setStreamEvents($streamEvents);

        $this->tracker->emit($event);

        if ($exception = $event->streamNotFound()) {
            throw $exception;
        }

        if ($exception = $event->concurrencyFailure()) {
            throw $exception;
        }
    }

    public function delete(StreamName $streamName): void
    {
        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new DeleteEvent($this->publisher));
        $event->setStreamName($streamName);

        $this->tracker->emit($event);

        if ($exception = $event->streamNotFound()) {
            throw $exception;
        }
    }

    public function updateStreamMetadata(StreamName $streamName, array $newMetadata): void
    {
        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new UpdateStreamMetadataEvent($this->publisher));
        $event->setStreamName($streamName);
        $event->setMetadata($newMetadata);

        $this->tracker->emit($event);

        if ($exception = $event->streamNotFound()) {
            throw $exception;
        }
    }

    public function load(StreamName $streamName,
                         int $fromNumber = 1,
                         int $count = null,
                         MetadataMatcher $metadataMatcher = null): \Iterator
    {
        if ($fromNumber < 1) {
            throw new InvalidArgumentException("From number parameter must be greater than 0");
        }

        if (null !== $count && $count < 1) {
            throw new InvalidArgumentException("Count parameter must be null or greater than 0");
        }

        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new LoadEvent($this->publisher));

        $event->setStreamName($streamName);
        $event->setCount($count);
        $event->setFromNumber($fromNumber);
        $event->setMetadataMatcher($metadataMatcher);

        $this->tracker->emit($event);

        if ($exception = $event->streamNotFound()) {
            throw $exception;
        }

        $streamEvents = $event->streamEvents();

        if (!$streamEvents) {
            throw new StreamNotFound("Unable to load stream name {$streamName}");
        }

        return $streamEvents;
    }

    public function loadReverse(StreamName $streamName,
                                int $fromNumber = null,
                                int $count = null,
                                MetadataMatcher $metadataMatcher = null): \Iterator
    {
        if (null !== $fromNumber && $fromNumber < 1) {
            throw new InvalidArgumentException("From number parameter must be null or greater than 0");
        }

        if (null !== $count && $count < 1) {
            throw new InvalidArgumentException("Count parameter must be null or greater than 0");
        }

        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new LoadReverseEvent($this->publisher));

        $event->setStreamName($streamName);
        $event->setCount($count);
        $event->setFromNumber($fromNumber);
        $event->setMetadataMatcher($metadataMatcher);

        $this->tracker->emit($event);

        if ($exception = $event->streamNotFound()) {
            throw $exception;
        }

        $streamEvents = $event->streamEvents();

        if (!$streamEvents) {
            throw new StreamNotFound("Unable to load stream name {$streamName}");
        }

        return $streamEvents;
    }

    public function hasStream(StreamName $streamName): bool
    {
        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new HasStreamEvent($this->publisher));
        $event->setStreamName($streamName);

        $this->tracker->emit($event);

        return $event->hasStreamResult();
    }

    public function fetchStreamNames(?string $filter,
                                     ?MetadataMatcher $metadataMatcher,
                                     int $limit = 20,
                                     int $offset = 0): array
    {
        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new FetchStreamNamesEvent($this->publisher));
        $event->setFilter($filter);
        $event->setMetadataMatcher($metadataMatcher);
        $event->setCount($limit);
        $event->setOffset($offset);

        $this->tracker->emit($event);

        return $event->streamNames();
    }

    public function fetchCategoryNames(?string $filter,
                                       ?MetadataMatcher $metadataMatcher,
                                       int $limit = 20,
                                       int $offset = 0): array
    {
        /** @var ChroniclerActionEvent $event */
        $event = $this->tracker->newActionEvent(new FetchCategoryNamesEvent($this->publisher));
        $event->setFilter($filter);
        $event->setMetadataMatcher($metadataMatcher);
        $event->setCount($limit);
        $event->setOffset($offset);

        $this->tracker->emit($event);

        return $event->categoryNames();
    }

    public function getInnerChronicler(): Chronicler
    {
        return $this->publisher;
    }
}