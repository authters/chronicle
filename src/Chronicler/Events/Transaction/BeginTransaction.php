<?php

namespace Authters\Chronicle\Chronicler\Events\Transaction;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class BeginTransaction extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'begin';
    }
}