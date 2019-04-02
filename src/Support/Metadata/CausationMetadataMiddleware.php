<?php

namespace Authters\Chronicle\Support\Metadata;

use Authters\Chronicle\Metadata\Causation\CausationMetadataEnricher;
use Authters\Chronicle\Chronicler\Events\AppendToEvent;
use Authters\Chronicle\Chronicler\Events\CreateEvent;
use Authters\Chronicle\Chronicler\Tracker\EventTracker;
use Authters\Chronicle\Chronicler\Tracker\ChroniclerActionEvent;
use Authters\Chronicle\Stream\Stream;
use Authters\ServiceBus\Contract\Envelope\Middleware;
use Authters\ServiceBus\Envelope\Envelope;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Contract\SubscribedEvent;
use Authters\Tracker\Event\AbstractSubscriber;
use Prooph\Common\Messaging\Message;

class CausationMetadataMiddleware implements Middleware
{
    /**
     * @var EventTracker
     */
    private $eventTracker;

    public function __construct(EventTracker $eventTracker)
    {
        $this->eventTracker = $eventTracker;
    }

    public function handle(Envelope $envelope, callable $next)
    {
        $message = $envelope->getMessage();

        if ($message instanceof Message) {
            $commandAware = new CausationMetadataEnricher($message);

            $this->eventTracker->subscribe($this->getCreateSubscriber($commandAware));
            $this->eventTracker->subscribe($this->getAppendSubscriber($commandAware));
        }

        $envelope = $next($envelope);

        return $envelope;
    }

    public function getCreateSubscriber(CausationMetadataEnricher $commandAware): SubscribedEvent
    {
        return new class($commandAware) extends AbstractSubscriber
        {
            /**
             * @var CausationMetadataEnricher
             */
            private $commandAware;

            public function __construct($commandAware)
            {
                $this->commandAware = $commandAware;
            }

            public function priority(): int
            {
                return 1000;
            }

            public function subscribeTo(): NamedEvent
            {
                return new CreateEvent();
            }

            public function applyTo(): callable
            {
                return function (ChroniclerActionEvent $event) {
                    $stream = $event->stream();
                    $recordedEvents = $stream->streamEvents();

                    $enrichedRecordedEvents = [];

                    foreach ($recordedEvents as $recordedEvent) {
                        $enrichedRecordedEvents[] = $this->commandAware->enrich($recordedEvent);
                    }

                    $stream = new Stream(
                        $stream->streamName(),
                        new \ArrayIterator($enrichedRecordedEvents),
                        $stream->metadata()
                    );

                    $event->setStream($stream);
                };
            }
        };
    }

    public function getAppendSubscriber(CausationMetadataEnricher $commandAware): SubscribedEvent
    {
        return new class($commandAware) extends AbstractSubscriber
        {
            /**
             * @var CausationMetadataEnricher
             */
            private $commandAware;

            public function __construct($commandAware)
            {
                $this->commandAware = $commandAware;
            }

            public function priority(): int
            {
                return 1000;
            }

            public function subscribeTo(): NamedEvent
            {
                return new AppendToEvent;
            }

            public function applyTo(): callable
            {
                return function (ChroniclerActionEvent $event) {
                    $recordedEvents = $event->streamEvents();

                    $enrichedRecordedEvents = [];

                    foreach ($recordedEvents as $recordedEvent) {
                        $enrichedRecordedEvents[] = $this->commandAware->enrich($recordedEvent);
                    }

                    $event->setStreamEvents(new \ArrayIterator($enrichedRecordedEvents));
                };
            }
        };
    }
}