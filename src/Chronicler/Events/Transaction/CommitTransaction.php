<?php

namespace Authters\Chronicle\Chronicler\Events\Transaction;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class CommitTransaction extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'commit';
    }
}