<?php

namespace Authters\Chronicle\Publisher\Events\Streams;

use Authters\Chronicle\Exceptions\ConcurrencyException;
use Authters\Chronicle\Exceptions\StreamNotFound;
use Authters\Chronicle\Publisher\Events\AppendToEvent;
use Authters\Chronicle\Publisher\Tracker\PublisherActionEvent;
use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnAppendToStreamPublisher extends AbstractSubscriber
{
    public function applyTo(): callable
    {
        return function (PublisherActionEvent $event) {
            /** @var Publisher $publisher */
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