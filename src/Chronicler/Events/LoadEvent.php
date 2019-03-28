<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class LoadEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'load';
    }
}