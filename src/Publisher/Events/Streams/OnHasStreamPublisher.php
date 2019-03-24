<?php

namespace Authters\Chronicle\Publisher\Events\Streams;

use Authters\Chronicle\Publisher\Events\HasStreamEvent;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnHasStreamPublisher extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (PublisherActionEvent $event) {
            /** @var Publisher $publisher */
            $publisher = $event->currentEvent()->target();

            $event->setStreamResult(
                $publisher->hasStream($event->streamName())
            );
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new HasStreamEvent;
    }

    public function priority(): int
    {
        return 1;
    }
}