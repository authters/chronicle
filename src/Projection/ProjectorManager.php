<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Projection\Factory\ProjectorOptions;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorContext;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorFactory;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorLock;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorRunner;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorContext;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorFactory;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorRunner;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelPersistentProjectorLock;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorContext;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorFactory;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorRunner;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectionManager;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjectorFactory as PersistentProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory as QueryProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjectorFactory as ReadModelProjector;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

class ProjectorManager implements ProjectionManager
{
    /**
     * @var ProjectorConnector
     */
    private $connector;

    public function __construct(ProjectorConnector $connector)
    {
        $this->connector = $connector;
    }

    public function createQuery(): QueryProjector
    {
        $context = new QueryProjectorContext(new ProjectorOptions());
        $runner = new QueryProjectorRunner($context, $this->connector);

        return new QueryProjectorFactory($context, $runner);
    }

    public function createProjection(string $name, array $options = []): PersistentProjector
    {
        $context = new ProjectionProjectorContext(new ProjectorOptions());
        $lock = new ProjectionProjectorLock(
            $this->connector->publisher(),
            $this->connector->projectionProvider(),
            $context,
            $name
        );

        $runner = new ProjectionProjectorRunner($context, $this->connector, $lock);

        return new ProjectionProjectorFactory(
            $context,
            $this->connector->publisher(),
            $lock,
            $runner,
            $name
        );
    }

    public function createReadModelProjection(string $name,
                                              ReadModel $readModel,
                                              array $options = []): ReadModelProjector
    {
        $context = new ReadModelProjectorContext(new ProjectorOptions());
        $lock = new ReadModelPersistentProjectorLock($context, $this->connector->projectionProvider(), $name, $readModel);
        $runner = new ReadModelProjectorRunner($context, $this->connector, $lock, $readModel);

        return new ReadModelProjectorFactory($context, $lock, $runner, $readModel, $name);
    }
}