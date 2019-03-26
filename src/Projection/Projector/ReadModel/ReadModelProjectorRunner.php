<?php

namespace Authters\Chronicle\Projection\Projector\ReadModel;

use Authters\Chronicle\Projection\Factory\PersistentProjectorContext;
use Authters\Chronicle\Projection\Factory\ProjectorLock;
use Authters\Chronicle\Projection\Factory\PersistentProjectorRunner;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

final class ReadModelProjectorRunner extends PersistentProjectorRunner
{
    /**
     * @var ReadModel
     */
    private $readModel;

    public function __construct(PersistentProjectorContext $builder,
                                ProjectorConnector $connector,
                                ProjectorLock $lock,
                                ReadModel $readModel)
    {
        $this->connector = $connector;
        $this->context = $builder;
        $this->lock = $lock;
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