<?php

namespace Authters\Chronicle\Projection\Projector\Projection;

use Authters\Chronicle\Projection\Factory\PersistentProjectorContext;
use Authters\Chronicle\Projection\Factory\PersistentProjectorRunner;
use Authters\Chronicle\Support\Contracts\Projection\ProjectorConnector;

class ProjectionProjectorRunner extends PersistentProjectorRunner
{
    public function __construct(PersistentProjectorContext $context,
                                ProjectorConnector $connector,
                                ProjectionProjectorLock $lock)
    {
        $this->connector = $connector;
        $this->context = $context;
        $this->lock = $lock;
    }
}