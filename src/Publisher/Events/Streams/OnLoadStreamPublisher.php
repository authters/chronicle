<?php

namespace Authters\Chronicle\Publisher\Events\Streams;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Publisher\Events\LoadEvent;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnLoadStreamPublisher extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (PublisherActionEvent $event) {
            /** @var Publisher $publisher */
            $publisher = $event->currentEvent()->target();

            try {
                $streamEvents = $publisher->load(
                    $event->streamName(),
                    $event->fromNumber(),
                    $event->count(),
                    $event->metadataMatcher()
                );
                $event->setStreamEvents($streamEvents);
            } catch (StreamNotFound $exception) {
                $event->setStreamNotFound($exception);
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new LoadEvent;
    }

    public function priority(): int
    {
        return 1;
    }
}