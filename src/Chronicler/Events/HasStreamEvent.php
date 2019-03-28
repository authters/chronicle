<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class HasStreamEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'has_stream';
    }
}