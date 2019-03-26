<?php

namespace Authters\Chronicle\Support\Contracts\Projection;


// change method names
// change params add metadata matcher to projection names to filter regex
use Authters\Chronicle\Projection\Factory\ProjectionStatus;

interface ProjectionReadManager
{
    public function fetchProjectionStatus(string $name): ProjectionStatus;

    public function fetchProjectionStreamPositions(string $name): array;

    public function fetchProjectionState(string $name): array;

    public function fetchProjectionNames(?string $filter, int $limit = 20, int $offset = 0): array;

    public function fetchProjectionNamesRegex(string $regex, int $limit = 20, int $offset = 0): array;
}