<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class DeleteEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'delete';
    }
}