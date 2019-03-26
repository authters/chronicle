<?php

namespace Authters\Chronicle\Support\Contracts\Projection;


// change method names
// change params add metadata matcher to projection names to filter regex
use Authters\Chronicle\Projection\Factory\ProjectionStatus;

interface ProjectionReadManager
{
    public function statusOf(string $name): ProjectionStatus;

    public function streamPositionsOf(string $name): array;

    public function stateOf(string $name): array;

    public function filterNamesOf(?string $filter, int $limit = 20, int $offset = 0): array;

    public function filterRegexOf(string $regex, int $limit = 20, int $offset = 0): array;
}