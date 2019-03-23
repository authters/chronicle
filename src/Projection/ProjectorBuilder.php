<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Projection\ReadModel\ReadModelHandlerContext;

class ProjectorBuilder
{
    /**
     * @var array
     */
    private $query;

    /**
     * @var \Closure
     */
    private $initCallback;

    /**
     * @var array|\Closure
     */
    private $handlers;

    public function __construct(array $query, $handlers, \Closure $initCallback = null)
    {
        $this->query = $query;
        $this->handlers = $handlers;
        $this->initCallback = $initCallback;
    }

    public function __invoke(ReadModelHandlerContext $context, ?string $currentStreamName): ?array
    {
        if ($this->hasSingleHandler()) {
            if (null !== $this->initCallback) {
                $callback = \Closure::bind($this->initCallback, $context($currentStreamName));
                $result = $callback();

                return \is_array($result) ? $result : null;
            }
        } else {
            foreach ($this->handlers as $eventName => $handler) {
                $this->handlers[$eventName] = \Closure::bind($handler, $context($currentStreamName));
            }
        }

        return null;
    }

    public function hasSingleHandler(): bool
    {
        return is_callable($this->handlers);
    }

    public function isQueryAll(): bool
    {
        return isset($this->query['all']);
    }

    public function isQueryCategories(): bool
    {
        return isset($this->query['categories']);
    }

    public function isQueryStreams(): bool
    {
        return !$this->isQueryAll() && !$this->isQueryCategories();
    }

    public function initCallback(): ?\Closure
    {
        return $this->initCallback;
    }
}