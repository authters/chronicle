<?php

namespace Authters\Chronicle\Chronicler\Events\Transaction;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class CommitTransaction extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'commit';
    }
}