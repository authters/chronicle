<?php

namespace Authters\Chronicle\Publisher\Connection;

use Authters\Chronicle\Stream\Stream;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

class ConnectionPublisher implements Publisher
{

    public function create(Stream $stream): void
    {
        // TODO: Implement create() method.
    }

    public function appendTo(StreamName $streamName, \Iterator $streamEvents): void
    {
        // TODO: Implement appendTo() method.
    }

    public function delete(StreamName $streamName): void
    {
        // TODO: Implement delete() method.
    }

    public function updateStreamMetadata(StreamName $streamName, array $newMetadata): void
    {
        // TODO: Implement updateStreamMetadata() method.
    }

    public function load(StreamName $streamName, int $fromNumber = 1, int $count = null, MetadataMatcher $metadataMatcher = null): \Iterator
    {
        // TODO: Implement load() method.
    }

    public function loadReverse(StreamName $streamName, int $fromNumber = null, int $count = null, MetadataMatcher $metadataMatcher = null): \Iterator
    {
        // TODO: Implement loadReverse() method.
    }

    public function fetchStreamNames(?string $filter, ?MetadataMatcher $metadataMatcher, int $limit = 20, int $offset = 0): array
    {
        // TODO: Implement fetchStreamNames() method.
    }

    public function fetchCategoryNames(?string $filter, ?MetadataMatcher $metadataMatcher, int $limit = 20, int $offset = 0): array
    {
        // TODO: Implement fetchCategoryNames() method.
    }

    public function hasStream(StreamName $streamName): bool
    {
        // TODO: Implement hasStream() method.
    }
}