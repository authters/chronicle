<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Projection\Factory\ProjectorOptions;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorContext;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorFactory;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorRunner;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorContext;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorFactory;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorLock;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorRunner;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectionManager;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory as Factory;
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

    public function createQuery(): Factory
    {
        $context = new QueryProjectorContext(new ProjectorOptions());
        $runner = new QueryProjectorRunner($context, $this->connector);

        return new QueryProjectorFactory($context, $runner);
    }

    public function createProjection(string $name, array $options = []): Factory
    {
        // TODO: Implement createProjection() method.
    }

    public function createReadModelProjection(string $name,
                                              ReadModel $readModel,
                                              array $options = []): Factory
    {
        $context = new ReadModelProjectorContext(new ProjectorOptions());
        $lock = new ReadModelProjectorLock($context, $this->connector->projectionProvider(), $name, $readModel);
        $runner = new ReadModelProjectorRunner($context, $this->connector, $lock, $readModel);

        return new ReadModelProjectorFactory($context, $lock, $runner, $readModel, $name);
    }
}