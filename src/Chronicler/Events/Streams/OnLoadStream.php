<?php

namespace Authters\Chronicle\Chronicler\Events\Streams;

use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Chronicler\Events\LoadEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnLoadStream extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            /** @var Chronicler $publisher */
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