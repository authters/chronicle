<?php

namespace Authters\Chronicle\Projection\ReadModel;

use Authters\Chronicle\Projection\ProjectorContextBuilder;
use Authters\Chronicle\Projection\ProjectorLock;
use Authters\Chronicle\Projection\ProjectorMutable;
use Authters\Chronicle\Projection\ProjectorOptions;
use Authters\Chronicle\Projection\ProjectorPersistentRunner;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

final class ReadModelProjectorRunner extends ProjectorPersistentRunner
{
    /**
     * @var ReadModel
     */
    private $readModel;

    public function __construct(ProjectorConnector $connector,
                                ProjectorContextBuilder $builder,
                                ProjectorLock $lock,
                                ProjectorMutable $mutable,
                                ProjectorOptions $options,
                                ReadModel $readModel)
    {
        $this->connector = $connector;
        $this->builder = $builder;
        $this->lock = $lock;
        $this->mutable = $mutable;
        $this->options = $options;
        $this->readModel = $readModel;
    }

    protected function prepareExecution(): void
    {
        if (!$this->lock->projectionExists()) {
            $this->lock->createProjection();
        }

        $this->lock->acquireLock();

        if (!$this->readModel->isInitialized()) {
            $this->readModel->init();
        }

        $this->prepareStreamPositions();

        $this->lock->load();
    }
}