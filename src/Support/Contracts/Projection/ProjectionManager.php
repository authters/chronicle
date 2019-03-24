<?php

namespace Authters\Chronicle\Support\Contracts\Projection;

use Authters\Chronicle\Projection\ReadModel\ReadModelProjectorFactory;
use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\PersistentProjector;
use Authters\Chronicle\Support\Contracts\Projection\Projector\QueryProjector;

interface ProjectionManager
{
    public function createQuery(): QueryProjector;

    public function createProjection(string $name, array $options = []): PersistentProjector;

    public function createReadModelProjection(string $name,
                                              ReadModel $readModel,
                                              array $options = []): ReadModelProjectorFactory;
}