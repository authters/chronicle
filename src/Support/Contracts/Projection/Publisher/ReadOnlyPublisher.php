<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Publisher;

use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;

interface ReadOnlyPublisher
{
    public function load(StreamName $streamName,
                         int $fromNumber = 1,
                         int $count = null,
                         MetadataMatcher $metadataMatcher = null): \Iterator;

    public function loadReverse(StreamName $streamName,
                                int $fromNumber = null,
                                int $count = null,
                                MetadataMatcher $metadataMatcher = null): \Iterator;

    public function fetchStreamNames(?string $filter,
                                     ?MetadataMatcher $metadataMatcher,
                                     int $limit = 20,
                                     int $offset = 0): array;

    public function fetchCategoryNames(?string $filter,
                                       ?MetadataMatcher $metadataMatcher,
                                       int $limit = 20,
                                       int $offset = 0): array;

    public function hasStream(StreamName $streamName): bool;
}