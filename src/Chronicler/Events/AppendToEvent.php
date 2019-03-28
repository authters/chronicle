<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class AppendToEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'append_to';
    }
}