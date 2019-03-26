<?php

namespace Authters\Chronicle\Projection\Projector\ReadModel;

use Authters\Chronicle\Projection\Factory\PersistentProjectorContext;
use Authters\Chronicle\Projection\Factory\PersistentProjectorLock;
use Authters\Chronicle\Projection\Factory\PersistentProjectorRunner;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

final class ReadModelProjectorRunner extends PersistentProjectorRunner
{
    /**
     * @var ReadModel
     */
    private $readModel;

    public function __construct(PersistentProjectorContext $context,
                                EventStreamProvider $eventStreamProvider,
                                Publisher $publisher,
                                PersistentProjectorLock $lock,
                                ReadModel $readModel)
    {
        parent::__construct($context, $eventStreamProvider, $publisher, $lock);

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