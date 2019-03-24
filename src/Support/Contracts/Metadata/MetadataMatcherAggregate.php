<?php

namespace Authters\Chronicle\Support\Contracts\Metadata;

interface MetadataMatcherAggregate
{
    public function matchAggregateIdAndType(string $aggregateId, string $aggregateType): MetadataMatcher;
}