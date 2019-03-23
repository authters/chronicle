<?php

namespace Authters\Chronicle\Support\Publisher;

use Authters\Tracker\Event\AbstractNamedEvent;

abstract class AbstractPublisherNamedEvent extends AbstractNamedEvent
{
    public function priority(): int
    {
       return 1;
    }
}