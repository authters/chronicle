<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class CreateEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'create';
    }
}