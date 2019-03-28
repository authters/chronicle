<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class DeleteEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'delete';
    }
}