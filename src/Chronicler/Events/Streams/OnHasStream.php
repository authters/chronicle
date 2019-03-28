<?php

namespace Authters\Chronicle\Chronicler\Events\Streams;

use Authters\Chronicle\Chronicler\Events\HasStreamEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnHasStream extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            /** @var Chronicler $publisher */
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