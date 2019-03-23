<?php

namespace Authters\Chronicle\Projection\Model;

use Authters\Chronicle\Projection\ProjectionStatus;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionModel;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Projection extends Model implements ProjectionModel, ProjectionProvider
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'no';

    /**
     * @var array
     */
    protected $guarded = [];

    public function newProjection(string $name, string $status): bool
    {
        $projection = $this->newInstance();

        $projection['name'] = $name;
        $projection['status'] = $status;
        $projection['position'] = '{}';
        $projection['state'] = '{}';
        $projection['locked_until'] = null;

        return $projection->save();
    }

    public function findByName(string $name): ?ProjectionModel
    {
        /** @var ProjectionModel $projection */
        $projection = $this->newInstance()->newQuery()->where('name', $name)->first();

        return $projection;
    }

    public function findByNames(?string $filter, int $limit, int $offset = 0): Collection
    {
        $query = $this->newInstance()->newQuery();

        if ($filter) {
            $query->where('name', $filter);
        }

        return $query
            ->orderBy('name')
            ->limit($limit)
            ->offset($offset)
            ->pluck('name');
    }

    public function findByNamesRegex(string $regex, int $limit, int $offset = 0): Collection
    {
        return $this->newInstance()->newQuery()
            ->whereRaw("name REGEXP $regex")
            ->orderBy('name')
            ->limit($limit)
            ->offset($offset)
            ->pluck('name');
    }

    public function acquireLock(string $name, string $status, string $lockedUntil, string $now): int
    {
        return $this->newInstance()->newQuery()
            ->where('name', $name)
            ->where(function (Builder $query) use ($now) {
                $query->whereRaw("locked_until IS NULL OR locked_until < ?", [$now]);
            })->update([
                'status' => $status,
                'locked_until' => $lockedUntil
            ]);
    }

    public function updateStatus(string $name, array $data): int
    {
        return $this->newInstance()->newQuery()
            ->where('name', $name)
            ->update($data);
    }

    public function deleteByName(string $name): int
    {
        return $this->newInstance()
            ->newQuery()
            ->where('name', $name)
            ->delete();
    }

    public function projectionExists(string $name): bool
    {
        return null !== $this->findByName($name);
    }

    public function getName(): string
    {
        return $this['name'];
    }

    public function getPosition(): string
    {
        return $this['position'];
    }

    public function getState(): string
    {
        return $this['state'];
    }

    public function getStatus(): ProjectionStatus
    {
        return ProjectionStatus::byValue($this['status']);
    }

    public function getLockedUntil(): ?string
    {
        return $this['locked_until'];
    }
}