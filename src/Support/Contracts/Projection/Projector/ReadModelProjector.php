<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Projector;

use Authters\Chronicle\Support\Contracts\Projection\Model\ReadModel;

interface ReadModelProjector extends PersistentProjector
{
    public function readModel(): ReadModel;
}