<?php

namespace Authters\Chronicle\Chronicler\Events\Streams;

use Authters\Chronicle\Exceptions\ConcurrencyException;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Chronicler\Events\AppendToEvent;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnAppendToStream extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (ChroniclerActionEvent $event) {
            /** @var Chronicler $publisher */
            $publisher = $event->currentEvent()->target();

            try {
                $publisher->appendTo(
                    $event->streamName(),
                    $event->streamEvents()
                );
            } catch (StreamNotFound $exception) {
                $event->setStreamNotFound($exception);
            } catch (ConcurrencyException $exception){
                $event->setConcurrencyFailure($exception);
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new AppendToEvent;
    }

    public function priority(): int
    {
        return '1';
    }
}