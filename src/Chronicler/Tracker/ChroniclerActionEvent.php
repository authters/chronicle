<?php

namespace Authters\Chronicle\Chronicler\Tracker;

use Authters\Chronicle\Exceptions\ConcurrencyException;
use Authters\Chronicle\Exceptions\StreamAlreadyExists;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Tracker\AbstractActionEvent;

class ChroniclerActionEvent extends AbstractActionEvent
{
    /**
     * @var Stream
     */
    private $stream;

    /**
     * @var StreamName
     */
    private $streamName;

    /**
     * @var array
     */
    private $streamNames = [];

    /**
     * @var array
     */
    private $categoryNames = [];

    /**
     * @var \Iterator
     */
    private $streamEvents;

    /**
     * @var bool
     */
    private $streamResult = false;

    /**
     * @var null|string
     */
    private $filter;

    /**
     * @var null|int
     */
    private $fromNumber;

    /**
     * @var null|int
     */
    private $count;

    /**
     * @var $offset
     */
    private $offset = 0;

    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @var MetadataMatcher
     */
    private $metadataMatcher;

    /**
     * @var StreamAlreadyExists
     */
    private $streamAlreadyExists;

    /**
     * @var StreamNotFound
     */
    private $streamNotFound;

    /**
     * @var ConcurrencyException
     */
    private $concurrencyFailure;

    public function setStream(Stream $stream): void
    {
        $this->stream = $stream;
    }

    public function stream(): ?Stream
    {
        return $this->stream;
    }

    public function setStreamName(StreamName $streamName): void
    {
        $this->streamName = $streamName;
    }

    public function streamName(): ?StreamName
    {
        return $this->streamName;
    }

    public function setStreamNames(array $streamNames): void
    {
        $this->streamNames = $streamNames;
    }

    public function streamNames(): array
    {
        return $this->streamNames;
    }

    public function setCategoryNames(array $categoryNames): void
    {
        $this->categoryNames = $categoryNames;
    }

    public function categoryNames(): array
    {
        return $this->categoryNames;
    }

    public function setStreamEvents(\Iterator $streamEvents): void
    {
        $this->streamEvents = $streamEvents;
    }

    public function streamEvents(): \Iterator
    {
        return $this->streamEvents ?? new \ArrayIterator();
    }

    public function setStreamResult(bool $streamResult): void
    {
        $this->streamResult = $streamResult;
    }

    public function hasStreamResult(): bool
    {
        return $this->streamResult;
    }

    public function setFilter(?string $filter): void
    {
        $this->filter = $filter;
    }

    public function filter(): ?string
    {
        return $this->filter;
    }

    public function setFromNumber(?int $fromNumber): void
    {
        $this->fromNumber = $fromNumber;
    }

    public function fromNumber(): ?int
    {
        return $this->fromNumber;
    }

    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

    public function count(): ?int
    {
        return $this->count;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function setMetadataMatcher(?MetadataMatcher $metadataMatcher): void
    {
        $this->metadataMatcher = $metadataMatcher;
    }

    public function metadataMatcher(): ?MetadataMatcher
    {
        return $this->metadataMatcher;
    }

    public function setStreamAlreadyExists(StreamAlreadyExists $exception): void
    {
        $this->streamAlreadyExists = $exception;
    }

    public function streamAlreadyExists(): ?StreamAlreadyExists
    {
        return $this->streamAlreadyExists;
    }

    public function setStreamNotFound(StreamNotFound $exception): void
    {
        $this->streamNotFound = $exception;
    }

    public function streamNotFound(): ?StreamNotFound
    {
        return $this->streamNotFound;
    }

    public function setConcurrencyFailure(ConcurrencyException $exception): void
    {
        $this->concurrencyFailure = $exception;
    }

    public function concurrencyFailure(): ?ConcurrencyException
    {
        return $this->concurrencyFailure;
    }
}