<?php

namespace Authters\Chronicle\Support\Projection;

class EventCounter
{
    /**
     * @var int
     */
    private $counter = 0;

    public function increment(): void
    {
        $this->counter++;
    }

    public function reset(): void
    {
        $this->counter = 0;
    }

    public function isReset(): bool
    {
        return 0 === $this->counter;
    }

    public function isEqualsTo(int $num): bool
    {
        return $this->counter === $num;
    }

    public function current(): int
    {
        return $this->counter;
    }
}