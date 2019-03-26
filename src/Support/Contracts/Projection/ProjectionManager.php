<?php

namespace Authters\Chronicle\Support\Contracts\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;
use Authters\Chronicle\Support\Contracts\Projection\Projector\ProjectorFactory;

interface ProjectionManager
{
    public function createQuery(): ProjectorFactory;

    public function createProjection(string $name, array $options = []): ProjectorFactory;

    public function createReadModelProjection(string $name,
                                              ReadModel $readModel,
                                              array $options = []): ProjectorFactory;
}