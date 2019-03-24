<?php

namespace Authters\Chronicle\Publisher\Events\Streams;

use Authters\Chronicle\Publisher\Events\FetchStreamNamesEvent;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnfetchStreamNamesPublisher extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (PublisherActionEvent $event) {
            /** @var Publisher $publiher */
            $publisher = $event->currentEvent()->target();

            $streamNames = $publisher->fetchStreamNames(
                $event->filter(),
                $event->metadataMatcher(),
                $event->count(),
                $event->offset()
            );

            $event->setStreamNames($streamNames);
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new FetchStreamNamesEvent;
    }

    public function priority(): int
    {
        return 1;
    }
}