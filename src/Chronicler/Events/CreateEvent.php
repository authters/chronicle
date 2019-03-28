<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class CreateEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'create';
    }
}