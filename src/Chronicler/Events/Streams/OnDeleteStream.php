<?php

namespace Authters\Chronicle\Chronicler\Events\Streams;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Chronicler\Events\DeleteEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnDeleteStream extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            /** @var Chronicler $publisher */
            $publisher = $event->currentEvent()->target();

            try {
                $publisher->delete($event->streamName());
            } catch (StreamNotFound $exception) {
                $event->setStreamNotFound($exception);
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new DeleteEvent;
    }

    public function priority(): int
    {
        return '1';
    }
}