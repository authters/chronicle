<?php

namespace Authters\Chronicle\Support\Contracts\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Model\EventStreamProvider;
use Authters\Chronicle\Support\Contracts\Projection\Model\ProjectionProvider;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

interface ProjectorConnector
{
    public function publisher(): Publisher;

    public function projectionProvider(): ProjectionProvider;

    public function eventStreamProvider(): EventStreamProvider;
}