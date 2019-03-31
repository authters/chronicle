<?php

namespace Authters\Chronicle\Chronicler\Events\BusTransaction;

use Authters\Chronicle\Support\Contracts\Projection\Chronicler\Chronicler;
use Authters\Chronicle\Support\Contracts\Projection\Chronicler\TransactionalEventChronicler;
use Authters\ServiceBus\Support\Events\Named\DispatchedEvent;
use Authters\Tracker\Contract\MessageActionEvent;
use Authters\Tracker\Contract\NamedEvent;
use Authters\Tracker\Event\AbstractSubscriber;

class OnMessageDispatchTransaction extends AbstractSubscriber
{
    /**
     * @var Chronicler
     */
    private $publisher;

    public function __construct(Chronicler $publisher)
    {
        $this->publisher = $publisher;
    }

    public function applyTo(): callable
    {
        return function (MessageActionEvent $event) {
            if (!$this->publisher instanceof TransactionalEventChronicler) {
                return;
            }

            $this->publisher->beginTransaction();
        };
    }

    public function subscribeTo(): NamedEvent
    {
        return new DispatchedEvent;
    }

    public function priority(): int
    {
        return 1000;
    }
}