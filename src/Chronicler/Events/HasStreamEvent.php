<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class HasStreamEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'has_stream';
    }
}