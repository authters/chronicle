<?php

namespace Authters\Chronicle\Stream;

use Authters\Chronicle\Exceptions\RuntimeException;

class StreamName
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        if ("" === $name || empty($name)) {
            throw new RuntimeException('Stream name can not be empty');
        }

        $this->name = $name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}