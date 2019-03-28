<?php

namespace Authters\Chronicle\Projection\Model;

use Authters\Chronicle\Exceptions\InvalidArgumentException;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamModel;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EventStream extends Model implements EventStreamModel, EventStreamProvider
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $guarded = [];

    public function newEventStream(array $data): bool
    {
        return $this->newInstance($data)->save();
    }

    public function findByCategories(array $categories): Collection
    {
        return $this->newInstance()->newQuery()
            ->whereIn('category', $categories)
            ->pluck('real_stream_name');
    }

    public function findAllExceptInternalStreams(): Collection
    {
        return $this->newInstance()->newQuery()
            ->whereRaw("real_stream_name NOT LIKE '$%'")
            ->pluck('real_stream_name');
    }

    public function filterStreamNames(?string $filter,
                                      ?MetadataMatcher $metadataMatcher,
                                      int $limit = 20,
                                      int $offset = 0): Collection
    {
        $query = $this->newInstance()->newQuery();

        if ($filter) {
            $query->where('real_stream_name', $filter);
        }

        if ($metadataMatcher) {
            $this->applyMetadataMatcherToBuilder($query, $metadataMatcher);
        }

        return $query
            ->orderBy('real_stream_name')
            ->limit($limit)
            ->offset($offset)
            ->pluck('real_stream_name');
    }

    public function filterCategoryNames(?string $filter,
                                        ?MetadataMatcher $metadataMatcher,
                                        int $limit = 20,
                                        int $offset = 0): Collection
    {
        $query = $this->newInstance()->newQuery();

        if ($filter) {
            $query->where('category', $filter);
        }

        $query->whereNotNull('category');

        if ($metadataMatcher) {
            $this->applyMetadataMatcherToBuilder($query, $metadataMatcher);
        }

        return $query
            ->orderBy('category')
            ->limit($limit)
            ->offset($offset)
            ->pluck('category');
    }

    public function deleteRealStreamName(string $realStreamName): int
    {
        return $this->newInstance()->newQuery()
            ->where('real_stream_name', $realStreamName)
            ->delete();
    }

    public function updateStreamMetadata(string $realStreamName, string $metadata): int
    {
        return $this->newInstance()->newQuery()
            ->where('real_stream_name', $realStreamName)
            ->update([
                'metadata' => $metadata
            ]);
    }

    public function hasRealStreamName(string $realStreamName): bool
    {
        return $this->newInstance()->newQuery()
            ->where('real_stream_name', $realStreamName)
            ->exists();
    }

    public function getId(): int
    {
        return $this->getKey();
    }

    public function realStreamName(): string
    {
        return $this['real_stream_name'];
    }

    public function streamName(): string
    {
        return $this['stream_name'];
    }

    public function metadata(): string
    {
        return $this['metadata'];
    }

    public function category(): string
    {
        return $this['category'];
    }

    protected function applyMetadataMatcherToBuilder(Builder $builder, MetadataMatcher $metadataMatcher): void
    {
        $queryBuilder = $this->newModelQuery()->getQuery();
        $whereClauses = $metadataMatcher->data();

        if (!is_callable($whereClauses)) {
            throw new InvalidArgumentException("Metadata matcher must be a callable");
        }

        $whereClauses($queryBuilder);
        $builder->addNestedWhereQuery($queryBuilder);
    }
}