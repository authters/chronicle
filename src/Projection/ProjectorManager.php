<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectionManager;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjector;
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
        // TODO: Implement createQuery() method.
    }

    public function createProjection(string $name, array $options = []): PersistentProjector
    {
        // TODO: Implement createProjection() method.
    }

    public function createReadModelProjection(string $name, ReadModel $readModel, array $options = []): ReadModelProjector
    {
        // TODO: Implement createReadModelProjection() method.
    }
}