<?php

namespace Authters\Chronicle\Publisher\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class CreateEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'create';
    }
}