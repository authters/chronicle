<?php

namespace Authters\Chronicle\Publisher\Events\Streams;

use Authters\Chronicle\Exceptions\StreamAlreadyExists;
use Authters\Chronicle\Publisher\Events\CreateEvent;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnCreateStreamPublisher extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (PublisherActionEvent $event) {
            /** @var Publisher $publisher */
            $publisher = $event->currentEvent()->target();
            try {
                $publisher->create($event->stream());
            } catch (StreamAlreadyExists $exception) {
                $event->setStreamAlreadyExists($exception);
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new CreateEvent;
    }

    public function priority(): int
    {
        return 1;
    }
}