<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class LoadReverseEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'load_reverse';
    }
}