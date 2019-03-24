<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Model;

use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Illuminate\Support\Collection;

interface EventStreamProvider
{
    public function newEventStream(array $data): bool;

    public function deleteRealStreamName(string $realStreamName): int;

    public function updateStreamMetadata(string $realStreamName, string $metadata): int;

    public function findByCategories(array $categories): Collection;

    public function findAllExceptInternalStreams(): Collection;

    public function filterStreamNames(?string $filter,
                                      ?MetadataMatcher $metadataMatcher,
                                      int $limit = 20,
                                      int $offset = 0): Collection;

    public function filterCategoryNames(?string $filter,
                                        ?MetadataMatcher $metadataMatcher,
                                        int $limit = 20,
                                        int $offset = 0): Collection;

    public function hasRealStreamName(string $realStreamName): bool;
}