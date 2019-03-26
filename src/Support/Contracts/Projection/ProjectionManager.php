<?php

namespace Authters\Chronicle\Support\Contracts\Projection;

use Authters\Chronicle\Exceptions\ProjectionNotFound;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectionProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjectorFactory;

interface ProjectionManager
{
    /**
     * @return ProjectorFactory|QueryProjector
     */
    public function createQuery(): ProjectorFactory;

    /**
     * @param string $name
     * @param array $options
     * @return PersistentProjectorFactory|ProjectionProjector
     */
    public function createProjection(string $name, array $options = []): PersistentProjectorFactory;

    /**
     * @param string $name
     * @param ReadModel $readModel
     * @param array $options
     * @return ReadModelProjectorFactory|ReadModelProjector
     */
    public function createReadModelProjection(string $name,
                                              ReadModel $readModel,
                                              array $options = []): ReadModelProjectorFactory;

    /**
     * @param string $name
     * @throws ProjectionNotFound
     */
    public function stopProjection(string $name): void;

    /**
     * @param string $name
     * @throws ProjectionNotFound
     */
    public function resetProjection(string $name): void;

    /**
     * @param string $name
     * @param bool $deleteEmittedEvents
     * @throws ProjectionNotFound
     */
    public function deleteProjection(string $name, bool $deleteEmittedEvents): void;
}