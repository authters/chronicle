<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorBuilder;
use Authters\Chronicle\Projection\ProjectorMutable;
use Authters\Chronicle\Projection\ProjectorOptions;
use Authters\Chronicle\Projection\ProjectorRunner;
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
     * @var ProjectorOptions
     */
    private $options;

    /**
     * @var ReadModel
     */
    private $readModel;

    /**
     * @var ProjectorBuilder
     */
    private $builder;

    /**
     * @var callable
     */
    private $context;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ProjectorRunner
     */
    private $runner;

    public function __construct(ProjectorConnector $connector,
                                ProjectorOptions $options,
                                ReadModel $readModel,
                                ProjectorBuilder $builder,
                                callable $context,
                                string $name)
    {
        $this->connector = $connector;
        $this->options = $options;
        $this->readModel = $readModel;
        $this->builder = $builder;
        $this->context = $context;
        $this->name = $name;

        $this->runner = $this->setupRunner();
    }

    public function project(bool $keepRunning): void
    {
        $this->runner->run($keepRunning);
    }

    public function readModel(): ReadModel
    {
        return $this->readModel;
    }

    private function setupRunner(): ProjectorRunner
    {
        $mutable = new ProjectorMutable();

        //apply context
        $state = ($this->builder)($this->context, null);
        $mutable->setState($state);

        $lock = new ReadModelProjectorLock(
            $this->builder,
            $this->connector->projectionProvider(),
            $mutable,
            $this->options,
            $this->name,
            $this->readModel
        );

        return new ProjectorRunner($lock, $mutable, $this->options, $this->connector->publisher());
    }
}