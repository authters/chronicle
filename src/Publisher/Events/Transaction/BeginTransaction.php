<?php

namespace Authters\Chronicle\Publisher\Events\Transaction;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class BeginTransaction extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'begin';
    }
}