<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Model;

use Illuminate\Support\Collection;

// checkMe use collection or array
interface ProjectionProvider
{
    public function newProjection(string $name, string $status): bool;

    public function updateStatus(string $name, array $data): int;

    public function acquireLock(string $name, string $status, string $lockedUntil, string $now): int;

    public function deleteByName(string $name): int;

    public function findByName(string $name): ?ProjectionModel;

    public function findByNames(?string $filter, int $limit, int $offset = 0): Collection;

    public function findByNamesRegex(string $regex, int $limit, int $offset = 0): Collection;

    public function projectionExists(string $name): bool;
}