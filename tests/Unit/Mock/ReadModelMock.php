<?php

namespace AuthtersTest\Chronicle\Unit\Mock;

use Authters\Chronicle\Exceptions\InvalidArgumentException;
use Authters\Chronicle\Support\Projection\ReadModel\AbstractReadModel;
use function array_key_exists;
use function is_array;

class ReadModelMock extends AbstractReadModel
{
    private $container;

    public function init(): void
    {
        $this->container = [];
    }

    public function insert(string $key, $data): void
    {
        $this->container[$key] = $data;
    }

    public function update(string $key, $data): void
    {
        if (!array_key_exists($key, $this->container)) {
            throw new InvalidArgumentException("Key $key not found");
        }
        $this->container[$key] = $data;
    }

    public function isInitialized(): bool
    {
        return \is_array($this->container);
    }

    public function reset(): void
    {
        $this->container = [];
    }

    public function delete(): void
    {
        $this->container = null;
    }

    public function hasKey(string $key): bool
    {
        return is_array($this->container) && array_key_exists($key, $this->container);
    }

    public function read(string $key)
    {
        return $this->container[$key];
    }
}