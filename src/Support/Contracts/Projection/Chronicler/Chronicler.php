<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Chronicler;

use Authters\Chronicle\Exceptions\ConcurrencyException;
use Authters\Chronicle\Exceptions\QueryChroniclerError;
use Authters\Chronicle\Exceptions\StreamAlreadyExists;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;

interface Chronicler extends ReadOnlyChronicler
{
    /**
     * @param Stream $stream
     * @throws ConcurrencyException
     * @throws QueryChroniclerError
     * @throws \Throwable
     */
    public function create(Stream $stream): void;

    /**
     * @param StreamName $streamName
     * @param \Iterator $streamEvents
     * @throws StreamAlreadyExists
     * @throws QueryChroniclerError
     * @throws \Exception|\Throwable
     */
    public function appendTo(StreamName $streamName, \Iterator $streamEvents): void;

    /**
     * @param StreamName $streamName
     * @throws StreamNotFound
     * @throws QueryChroniclerError
     * @throws \Exception
     */
    public function delete(StreamName $streamName): void;

    /**
     * @param StreamName $streamName
     * @param array $newMetadata
     * @throws StreamNotFound
     * @throws QueryChroniclerError
     * @throws \Exception
     */
    public function updateStreamMetadata(StreamName $streamName, array $newMetadata): void;
}