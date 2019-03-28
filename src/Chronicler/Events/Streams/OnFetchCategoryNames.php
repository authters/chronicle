<?php

namespace Authters\Chronicle\Chronicler\Events\Streams;

use Authters\Chronicle\Chronicler\Events\FetchCategoryNamesEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnFetchCategoryNames extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            /** @var Chronicler $publisher */
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