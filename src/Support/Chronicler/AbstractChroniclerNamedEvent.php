<?php

namespace Authters\Chronicle\Support\Chronicler;

use Authters\Tracker\Event\AbstractNamedEvent;

abstract class AbstractChroniclerNamedEvent extends AbstractNamedEvent
{
    public function priority(): int
    {
       return 1;
    }
}