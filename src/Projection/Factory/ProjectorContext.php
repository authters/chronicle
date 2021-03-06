<?php

namespace Authters\Chronicle\Projection\Factory;

use Authters\Chronicle\Exceptions\RuntimeException;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorOptions;
use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Projector\Projector;
use Authters\Chronicle\Support\Projection\StreamPositions;

abstract class ProjectorContext
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

    /**
     * @var MetadataMatcher
     */
    private $metadataMatcher;

    /**
     * @var StreamPositions
     */
    protected $streamPositions;

    /**
     * @var ProjectionStatus
     */
    protected $status;

    /**
     * @var array
     */
    protected $state;

    /**
     * @var boolean
     */
    protected $isStopped;

    /**
     * @var ?string
     */
    protected $currentStreamName;

    /**
     * @var EventStreamProvider
     */
    private $eventStreamProvider;

    /**
     * @var ProjectorOptions
     */
    protected $options;

    public function __construct(EventStreamProvider $eventStreamProvider, ProjectorOptions $options)
    {
        $this->eventStreamProvider = $eventStreamProvider;
        $this->options = $options;
        $this->streamPositions = new StreamPositions();
        $this->status = ProjectionStatus::IDLE();
        $this->isStopped = false;
        $this->currentStreamName = null;
        $this->state = [];
    }

    public function prepareStreamPositions(): void
    {
        if ($this->isQueryCategories()) {
            $realStreamNames = $this->eventStreamProvider
                ->findByCategories($this->queryCategories())
                ->toArray();
        } elseif ($this->isQueryAll()) {
            $realStreamNames = $this->eventStreamProvider
                ->findAllExceptInternalStreams()
                ->toArray();
        } else {
            $realStreamNames = $this->queryStreams();
        }

        $this->streamPositions->prepareStreamPositions($realStreamNames);
    }

    public function __invoke(Projector $projector, ?string $currentStreamName): void
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

            $this->initCallback = $callback;

            if (\is_array($result)) {
                $this->setState($result);
            }
        }
    }

    public function stop(bool $stop): void
    {
        $this->isStopped = $stop;
    }

    public function isStopped(): bool
    {
        return $this->isStopped;
    }

    public function streamPositions(): StreamPositions
    {
        return $this->streamPositions;
    }

    public function setStatus(ProjectionStatus $status): void
    {
        $this->status = $status;
    }

    public function status(): ProjectionStatus
    {
        return $this->status;
    }

    public function resetState(): void
    {
        $this->state = [];
    }

    public function setState($result = null): void
    {
        if (\is_array($result)) {
            $this->state = $result;
        }
    }

    public function state(): array
    {
        return $this->state;
    }

    public function currentStreamName(): ?string
    {
        return $this->currentStreamName;
    }

    public function setStreamName(string $streamName = null): void
    {
        $this->currentStreamName = $streamName;
    }

    public function hasSingleHandler(): bool
    {
        return \is_callable($this->handlers);
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
            throw new RuntimeException('Projection handlers already set');
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

    public function setFrom(string $key, array $names = []): void
    {
        if (null !== $this->query) {
            throw new RuntimeException('Projection streams already set');
        }

        if (!in_array($key, ['streams', 'categories', 'all'])) {
            throw new RuntimeException("Invalid item from {$key}");
        }

        if ('all' === $key) {
            $this->query['all'] = true;
        } else {
            foreach ($names as $name) {
                $this->query[$key][] = $name;
            }
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

    /**
     * @return ProjectorOptions|PersistentProjectorOptions|ProjectionProjectorOptions
     */
    public function options(): ProjectorOptions
    {
        return $this->options;
    }

    abstract protected function createHandlerContext(Projector $projector, ?string &$streamName): object;
}