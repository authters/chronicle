<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class LoadEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'load';
    }
}