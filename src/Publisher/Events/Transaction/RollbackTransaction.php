<?php

namespace Authters\Chronicle\Publisher\Events\Transaction;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class RollbackTransaction extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
       return 'rollback';
    }
}