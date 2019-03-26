<?php

namespace Authters\Chronicle\Support\Contracts\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ReadModelProjectorFactory;

interface ProjectionManager
{
    public function createQuery(): ProjectorFactory;

    public function createProjection(string $name, array $options = []): PersistentProjectorFactory;

    public function createReadModelProjection(string $name,
                                              ReadModel $readModel,
                                              array $options = []): ReadModelProjectorFactory;
}