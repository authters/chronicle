<?php

namespace Authters\Chronicle\Chronicler\Events\Streams;

use Authters\Chronicle\Chronicler\Events\FetchStreamNamesEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnfetchStreamNames extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            /** @var Chronicler $chronicler */
            $chronicler = $event->currentEvent()->target();

            $streamNames = $chronicler->fetchStreamNames(
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