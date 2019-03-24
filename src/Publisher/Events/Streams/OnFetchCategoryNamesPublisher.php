<?php

namespace Authters\Chronicle\Publisher\Events\Streams;

use Authters\Chronicle\Publisher\Events\FetchCategoryNamesEvent;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnFetchCategoryNamesPublisher extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (PublisherActionEvent $event) {
            /** @var Publisher $publisher */
            $publisher = $event->currentEvent()->target();

            $categoryNames = $publisher->fetchCategoryNames(
                $event->filter(),
                $event->metadataMatcher(),
                $event->count(),
                $event->offset()
            );

            $event->setCategoryNames($categoryNames);
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new FetchCategoryNamesEvent;
    }

    public function priority(): int
    {
        return 1;
    }
}