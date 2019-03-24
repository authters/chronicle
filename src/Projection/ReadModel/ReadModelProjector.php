<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorContextBuilder;
use Authters\Chronicle\Projection\ProjectorMutable;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjector as BaseProjector;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

class ReadModelProjector implements BaseProjector
{
    /**
     * @var ProjectorConnector
     */
    private $connector;

    /**
     * @var ReadModel
     */
    private $readModel;

    /**
     * @var ProjectorContextBuilder
     */
    private $builder;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ProjectorMutable
     */
    private $mutable;

    /**
     * @var ReadModelProjectorLock
     */
    private $lock;

    /**
     * @var ReadModelProjectorRunner
     */
    private $runner;

    public function __construct(ProjectorConnector $connector,
                                ReadModel $readModel,
                                ReadModelProjectorContextBuilder $builder,
                                string $name)
    {
        $this->connector = $connector;
        $this->readModel = $readModel;
        $this->builder = $builder;
        $this->name = $name;
        $this->mutable = new ProjectorMutable();

        $this->setupRunner();
    }

    public function run(bool $keepRunning = true): void
    {
        $this->runner->run($keepRunning);
    }

    public function reset(): void
    {
        $this->lock->reset();
    }

    public function stop(): void
    {
        $this->lock->stop();
    }

    public function delete(bool $deleteEmittedEvents): void
    {
        $this->lock->delete($deleteEmittedEvents);
    }

    public function readModel(): ReadModel
    {
        return $this->readModel;
    }

    public function getState(): array
    {
        return $this->mutable->state();
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function setupRunner(): void
    {
        $this->lock = new ReadModelProjectorLock(
            $this->builder,
            $this->connector->projectionProvider(),
            $this->mutable,
            $this->name,
            $this->readModel
        );

        $this->runner = new ReadModelProjectorRunner(
            $this->connector,
            $this->builder,
            $this->lock,
            $this->mutable,
            $this->readModel
        );

        // apply context
        $result = ($this->builder)($this, $this->mutable->currentStreamName());
        $this->mutable->setState($result);
    }
}