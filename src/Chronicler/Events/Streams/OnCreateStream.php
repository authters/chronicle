<?php

namespace Authters\Chronicle\Chronicler\Events\Streams;

use Authters\Chronicle\Exceptions\StreamAlreadyExists;
use Authters\Chronicle\Chronicler\Events\CreateEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnCreateStream extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            /** @var Chronicler $publisher */
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