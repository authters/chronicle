<?php

namespace Authters\Chronicle\Chronicler\Events\BusTransaction;

use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalEventChronicler;
use Authters\ServiceBus\Support\Events\Named\FinalizedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnMessageFinalizeDispatchTransaction extends AbstractSubscriber
{
    /**
     * @var Chronicler|TransactionalEventChronicler
     */
    private $publisher;

    public function __construct(Chronicler $publisher)
    {
        $this->publisher = $publisher;
    }

    public function applyTo(): callable
    {
        return function (MessageActionEvent $event) {
            if ($this->inTransaction()) {
                if ($event->exception()) {
                    $this->publisher->rollbackTransaction();
                } else {
                    $this->publisher->commitTransaction();
                }
            }
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new FinalizedEvent;
    }

    public function priority(): int
    {
        return 1000;
    }

    private function inTransaction(): bool
    {
        return $this->publisher instanceof TransactionalEventChronicler
            && $this->publisher->inTransaction();
    }
}