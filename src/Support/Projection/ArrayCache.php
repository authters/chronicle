<?php

namespace Authters\Chronicle\Support\Projection;

class ArrayCache
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $position = -1;

    public function __construct(int $size)
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Size must be a positive integer');
        }

        $this->size = $size;
        $this->container = \array_fill(0, $size, null);
    }

    /**
     * @param mixed $value
     */
    public function rollingAppend($value): void
    {
        $this->container[$this->nextPosition()] = $value;
    }

    /**
     * @param int $position
     * @return mixed
     */
    public function get(int $position)
    {
        if ($position >= $this->size || $position < 0) {
            throw new \InvalidArgumentException('Position must be between 0 and ' . ($this->size - 1));
        }

        return $this->container[$position];
    }

    public function has($value): bool
    {
        return \in_array($value, $this->container, true);
    }

    public function size(): int
    {
        return $this->size;
    }

    private function nextPosition(): int
    {
        return $this->position = ++$this->position % $this->size;
    }
}