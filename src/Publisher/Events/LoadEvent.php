<?php

namespace Authters\Chronicle\Publisher\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class LoadEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'load';
    }
}