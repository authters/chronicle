<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Exceptions\ProjectionNotFound;
use Authters\Chronicle\Projection\Factory\ProjectionStatus;
use Authters\Chronicle\Projection\Factory\ProjectorOptions;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorContext;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorFactory;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorLock;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorOptions;
use Authters\Chronicle\Projection\Projector\Projection\ProjectionProjectorRunner;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorContext;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorFactory;
use Authters\Chronicle\Projection\Projector\Query\QueryProjectorRunner;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorContext;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorFactory;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorLock;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorOptions;
use Authters\Chronicle\Projection\Projector\ReadModel\ReadModelProjectorRunner;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\ProjectionManager;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjectorFactory as PersistentProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory as QueryProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjectorFactory as ReadModelProjector;
use Authters\Chronicle\Support\Json;

class ProjectorManager implements ProjectionManager
{
    /**
     * @var ProjectionProvider
     */
    private $projectionProvider;

    /**
     * @var EventStreamProvider
     */
    private $eventStreamProvider;

    /**
     * @var Chronicler
     */
    private $publisher;

    public function __construct(ProjectionProvider $projectionProvider,
                                EventStreamProvider $eventStreamProvider,
                                Chronicler $publisher)
    {
        $this->projectionProvider = $projectionProvider;
        $this->eventStreamProvider = $eventStreamProvider;
        $this->publisher = $publisher;
    }

    public function createQuery(array $options = []): QueryProjector
    {
        $context = new QueryProjectorContext($this->eventStreamProvider, new ProjectorOptions($options));

        $runner = new QueryProjectorRunner($context, $this->publisher);

        return new QueryProjectorFactory($context, $runner);
    }

    public function createProjection(string $name, array $options = []): PersistentProjector
    {
        $context = new ProjectionProjectorContext($this->eventStreamProvider, new ProjectionProjectorOptions($options));

        $lock = new ProjectionProjectorLock(
            $this->publisher,
            $this->projectionProvider,
            $context,
            $name
        );

        $runner = new ProjectionProjectorRunner($context, $this->publisher, $lock);

        return new ProjectionProjectorFactory(
            $context,
            $this->publisher,
            $lock,
            $runner,
            $name
        );
    }

    public function createReadModelProjection(string $name,
                                              ReadModel $readModel,
                                              array $options = []): ReadModelProjector
    {
        $context = new ReadModelProjectorContext($this->eventStreamProvider, new ReadModelProjectorOptions($options));

        $lock = new ReadModelProjectorLock($context, $this->projectionProvider, $name, $readModel);

        $runner = new ReadModelProjectorRunner($context, $this->publisher, $lock, $readModel);

        return new ReadModelProjectorFactory($context, $lock, $runner, $readModel, $name);
    }

    public function stopProjection(string $name): void
    {
        $result = $this->projectionProvider->updateStatus($name, ['status' => ProjectionStatus::STOPPING]);

        if (0 === $result) {
            $this->assertProjectionNameExists($name);
        }
    }

    public function resetProjection(string $name): void
    {
        $result = $this->projectionProvider->updateStatus($name, ['status' => ProjectionStatus::RESETTING]);

        if (0 === $result) {
            $this->assertProjectionNameExists($name);
        }
    }

    public function deleteProjection(string $name, bool $deleteEmittedEvents): void
    {
        $status = $deleteEmittedEvents
            ? ProjectionStatus::DELETING_INCL_EMITTED_EVENTS
            : ProjectionStatus::DELETING;

        $result = $this->projectionProvider->updateStatus($name, ['status' => $status]);

        if (0 === $result) {
            $this->assertProjectionNameExists($name);
        }
    }

    public function statusOf(string $name): ProjectionStatus
    {
        $result = $this->projectionProvider->findByName($name);

        if (!$result) {
            throw new ProjectionNotFound("Projection name $name not found");
        }

        return $result->getStatus();
    }

    public function streamPositionsOf(string $name): array
    {
        $result = $this->projectionProvider->findByName($name);

        if (!$result) {
            throw new ProjectionNotFound("Projection name $name not found");
        }

        return Json::decode($result->getPosition());
    }

    public function stateOf(string $name): array
    {
        $result = $this->projectionProvider->findByName($name);

        if (!$result) {
            throw new ProjectionNotFound("Projection name $name not found");
        }

        return Json::decode($result->getState());
    }

    public function filterNamesOf(?string $filter, int $limit = 20, int $offset = 0): array
    {
        $result = $this->projectionProvider->findByNames($filter, $limit, $offset);

        return $result->toArray();
    }

    public function filterRegexOf(string $regex, int $limit = 20, int $offset = 0): array
    {
        $result = $this->projectionProvider->findByNamesRegex($regex, $limit, $offset);

        return $result->toArray();
    }

    protected function assertProjectionNameExists(string $name): void
    {
        $result = $this->projectionProvider->findByName($name);

        if (!$result) {
            throw new ProjectionNotFound("Projection name $name not found");
        }
    }
}