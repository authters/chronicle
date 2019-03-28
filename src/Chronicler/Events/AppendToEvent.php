<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class AppendToEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'append_to';
    }
}