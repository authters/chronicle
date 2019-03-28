<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class LoadReverseEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'load_reverse';
    }
}