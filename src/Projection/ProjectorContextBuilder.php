<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Exceptions\RuntimeException;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Projection\Projector\Projector;

abstract class ProjectorContextBuilder
{
    /**
     * @var ProjectorOptions
     */
    private $options;

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

    /**
     * @var MetadataMatcher
     */
    private $metadataMatcher;

    public function __construct(ProjectorOptions $options)
    {
        $this->options = $options;
    }

    public function __invoke(Projector $projector, ?string $currentStreamName): ?array
    {
        if ($this->hasSingleHandler()) {
            $this->handlers = \Closure::bind(
                $this->handlers,
                $this->createHandlerContext($projector, $currentStreamName)
            );
        } else {
            foreach ($this->handlers as $eventName => $handler) {
                $this->handlers[$eventName] = \Closure::bind(
                    $handler,
                    $this->createHandlerContext($projector, $currentStreamName)
                );
            }
        }

        if (null !== $this->initCallback) {
            $callback = \Closure::bind(
                $this->initCallback,
                $this->createHandlerContext($projector, $currentStreamName)
            );

            $result = $callback();
            if (\is_array($result)) {
                return $result;
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

    public function setInitCallback(\Closure $callback): void
    {
        if (null !== $this->initCallback) {
            throw new RuntimeException('Projection already initialized');
        }

        $this->initCallback = $callback;
    }

    public function initCallback(): ?\Closure
    {
        return $this->initCallback;
    }

    public function setHandlers($handlers): void
    {
        if (null !== $this->handlers) {
            throw new RuntimeException('Projection already initialized');
        }

        $this->handlers = $handlers;
    }

    public function handlers(): iterable
    {
        return $this->handlers;
    }

    public function singleHandler(): \Closure
    {
        return $this->handlers;
    }

    public function setStreamNames(string ...$streamNames): void
    {
        if (null !== $this->query) {
            throw new RuntimeException('From was already called');
        }

        foreach ($streamNames as $streamName) {
            $this->query['streams'][] = $streamName;
        }
    }

    public function queryStreams(): array
    {
        if ($this->isQueryStreams()) {
            return $this->query['streams'];
        }

        throw new RuntimeException("Query streams not set");
    }

    public function queryCategories(): array
    {
        if ($this->isQueryCategories()) {
            return $this->query['categories'];
        }

        throw new RuntimeException("Query categories not set");
    }

    public function setMetadataMatcher(MetadataMatcher $metadataMatcher = null): void
    {
        $this->metadataMatcher = $metadataMatcher;
    }

    public function metadataMatcher(): ?MetadataMatcher
    {
        return $this->metadataMatcher;
    }

    public function options(): ProjectorOptions
    {
        return $this->options;
    }

    abstract protected function createHandlerContext(Projector $projector, ?string &$streamName): object;
}