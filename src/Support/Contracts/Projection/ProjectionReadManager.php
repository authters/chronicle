<?php

namespace Authters\Chronicle\Support\Contracts\Projection;

use Authters\Chronicle\Projection\Factory\ProjectionStatus;

interface ProjectionReadManager
{
    /**
     * @param string $name
     * @return ProjectionStatus
     */
    public function statusOf(string $name): ProjectionStatus;

    /**
     * @param string $name
     * @return array
     */
    public function streamPositionsOf(string $name): array;

    /**
     * @param string $name
     * @return array
     */
    public function stateOf(string $name): array;

    /**
     * @param string|null $filter
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function filterNamesOf(?string $filter, int $limit = 20, int $offset = 0): array;

    public function filterRegexOf(string $regex, int $limit = 20, int $offset = 0): array;
}