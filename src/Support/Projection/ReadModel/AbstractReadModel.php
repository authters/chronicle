<?php

namespace Authters\Chronicle\Support\Projection\ReadModel;

use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;

abstract class AbstractReadModel implements ReadModel
{
    /**
     * @var array
     */
    private $stack = [];

    public function stack(string $operation, ...$args): void
    {
        $this->stack[] = [$operation, $args];
    }

    public function persist(): void
    {
        foreach ($this->stack as [$operation, $args]) {
            $this->{$operation}(...$args);
        }

        $this->stack = [];
    }
}