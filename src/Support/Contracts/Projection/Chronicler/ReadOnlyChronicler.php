<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Chronicler;

use Authters\Chronicle\Exceptions\QueryChroniclerError;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Stream\StreamName;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;

interface ReadOnlyChronicler
{
    /**
     * @param StreamName $streamName
     * @param int $fromNumber
     * @param int|null $count
     * @param MetadataMatcher|null $metadataMatcher
     * @return \Iterator
     * @throws StreamNotFound
     * @throws QueryChroniclerError
     */
    public function load(StreamName $streamName,
                         int $fromNumber = 1,
                         int $count = null,
                         MetadataMatcher $metadataMatcher = null): \Iterator;

    /**
     * @param StreamName $streamName
     * @param int|null $fromNumber
     * @param int|null $count
     * @param MetadataMatcher|null $metadataMatcher
     * @return \Iterator
     *
     * @throws StreamNotFound
     * @throws QueryChroniclerError
     */
    public function loadReverse(StreamName $streamName,
                                int $fromNumber = null,
                                int $count = null,
                                MetadataMatcher $metadataMatcher = null): \Iterator;

    /**
     * @param string|null $filter
     * @param MetadataMatcher|null $metadataMatcher
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function fetchStreamNames(?string $filter,
                                     ?MetadataMatcher $metadataMatcher,
                                     int $limit = 20,
                                     int $offset = 0): array;

    /**
     * @param string|null $filter
     * @param MetadataMatcher|null $metadataMatcher
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function fetchCategoryNames(?string $filter,
                                       ?MetadataMatcher $metadataMatcher,
                                       int $limit = 20,
                                       int $offset = 0): array;

    /**
     * @param StreamName $streamName
     * @return bool
     */
    public function hasStream(StreamName $streamName): bool;
}