<?php

namespace Authters\Chronicle\Support\Connection;

use Authters\Chronicle\Metadata\Matcher\CallableMetadataMatcher;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcherAggregate;
use Illuminate\Database\Query\Builder;

class ConnectionMetadataMatchers implements MetadataMatcherAggregate
{
    public function matchAggregateIdAndType(string $aggregateId, string $aggregateType): MetadataMatcher
    {
        return new CallableMetadataMatcher(
            function (Builder $builder) use ($aggregateId, $aggregateType) {
                $builder->whereJsonContains('metadata->_aggregate_id', $aggregateId);
                $builder->whereJsonContains('metadata->_aggregate_type', $aggregateType);
            }
        );
    }
}