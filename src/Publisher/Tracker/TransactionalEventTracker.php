<?php

namespace Authters\Chronicle\Publisher\Tracker;

use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\NamedEvent;

class TransactionalEventTracker extends EventTracker
{
    public function newActionEvent(NamedEvent $event, callable $callback = null): ActionEvent
    {
        return new TransactionalPublisherActionEvent($event, $callback);
    }
}