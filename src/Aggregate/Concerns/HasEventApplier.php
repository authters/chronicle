<?php

namespace Authters\Chronicle\Aggregate\Concerns;

use Authters\Chronicle\Aggregate\AggregateChanged;
use Authters\Chronicle\Exceptions\RuntimeException;

trait HasEventApplier
{
    public function apply(AggregateChanged $event): void
    {
        $handler = $this->determineHandlerMethodFor($event);

        if (!method_exists($this, $handler)) {
            $message = "Missing event handler method " . $handler;
            $message .= " for aggregate " . \get_class($this);

            throw new RuntimeException($message);
        }

        $this->{$handler}($event);
    }

    protected function determineHandlerMethodFor(AggregateChanged $event): string
    {
        return 'when' . class_basename($event);
    }
}