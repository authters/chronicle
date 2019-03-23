<?php

namespace Authters\Chronicle\Publisher\Tracker;

use Authters\Tracker\Contract\ActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\DefaultTracker;

class EventTracker extends DefaultTracker
{
    public function newActionEvent(NamedEvent $event, callable $callback = null): ActionEvent
    {
        return new PublisherActionEvent($event, $callback);
    }
}