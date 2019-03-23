<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Exceptions\RuntimeException;

abstract class ProjectorFactory
{
    /**
     * @var array|null
     */
    protected $query;

    /**
     * @var callable|null
     */
    protected $initCallback;

    /**
     * @var iterable|\Closure
     */
    protected $handlers;

    public function init(\Closure $callback): self
    {
        if (null !== $this->initCallback) {
            throw new RuntimeException('Projection already initialized');
        }

        $this->initCallback = $callback;

        return $this;
    }

    public function fromStreams(string ...$streamNames): self
    {
        if (null !== $this->query) {
            throw new RuntimeException('From was already called');
        }

        foreach ($streamNames as $streamName) {
            $this->query['streams'][] = $streamName;
        }

        return $this;
    }

    public function when(iterable $handlers): self
    {
        if (null !== $this->handlers) {
            throw new RuntimeException('When was already called');
        }

        $this->handlers = $handlers;

        return $this;
    }

    public function whenAny(\Closure $singleHandler): self
    {
        if (null !== $this->handlers) {
            throw new RuntimeException('When was already called');
        }

        $this->handlers = $singleHandler;

        return $this;
    }
}