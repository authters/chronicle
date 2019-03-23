<?php

namespace Authters\Chronicle\Publisher\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class DeleteEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'delete';
    }
}