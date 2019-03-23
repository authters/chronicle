<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Publisher;

use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;

interface Publisher extends ReadOnlyPublisher
{
    public function create(Stream $stream): void;

    public function appendTo(StreamName $streamName, \Iterator $streamEvents): void;

    public function delete(StreamName $streamName): void;

    public function updateStreamMetadata(StreamName $streamName, array $newMetadata): void;
}