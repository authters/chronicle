<?php

namespace Authters\Chronicle\Aggregate;

use Authters\Chronicle\Exceptions\InvalidArgumentException;

class AggregateType
{
    /**
     * @var string
     */
    private $modelType;

    public static function fromRoot(object $root): AggregateType
    {
        $self = new self();
        $self->modelType = \get_class($root);

        return $self;
    }

    /**
     * @param string $root
     * @return AggregateType
     * @throws InvalidArgumentException
     */
    public static function fromRootClass(string $root): AggregateType
    {
        if (!\class_exists($root)) {
            throw new InvalidArgumentException("Model root class $root not found");
        }

        $self = new self();
        $self->modelType = $root;

        return $self;
    }

    /**
     * @param object $root
     * @throws InvalidArgumentException
     */
    public function assert(object $root): void
    {
        $aRoot = self::fromRoot($root);

        if ($aRoot->toString() !== $this->toString()) {
            throw new InvalidArgumentException("Model types are not equal");
        }
    }

    public function toString(): string
    {
        return $this->modelType;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function __construct()
    {
    }
}