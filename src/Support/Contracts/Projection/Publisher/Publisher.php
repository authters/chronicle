<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Publisher;

use Authters\Chronicle\Exceptions\ConcurrencyException;
use Authters\Chronicle\Exceptions\QueryPublisherError;
use Authters\Chronicle\Exceptions\StreamAlreadyExists;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;

interface Publisher extends ReadOnlyPublisher
{
    /**
     * @param Stream $stream
     * @throws ConcurrencyException
     * @throws QueryPublisherError
     * @throws \Throwable
     */
    public function create(Stream $stream): void;

    /**
     * @param StreamName $streamName
     * @param \Iterator $streamEvents
     * @throws StreamAlreadyExists
     * @throws QueryPublisherError
     * @throws \Exception|\Throwable
     */
    public function appendTo(StreamName $streamName, \Iterator $streamEvents): void;

    /**
     * @param StreamName $streamName
     * @throws StreamNotFound
     * @throws QueryPublisherError
     * @throws \Exception
     */
    public function delete(StreamName $streamName): void;

    /**
     * @param StreamName $streamName
     * @param array $newMetadata
     * @throws StreamNotFound
     * @throws QueryPublisherError
     * @throws \Exception
     */
    public function updateStreamMetadata(StreamName $streamName, array $newMetadata): void;
}