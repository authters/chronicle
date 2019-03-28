<?php

namespace Authters\Chronicle\Aggregate\Model;

use Authters\Chronicle\Aggregate\AggregateChanged;
use Authters\Chronicle\Aggregate\Concerns\HasEventProducer;
use Authters\Chronicle\Aggregate\Concerns\HasEventSourced;
use Authters\Chronicle\Exceptions\RuntimeException;
use Illuminate\Database\Eloquent\Model;

abstract class AggregateModelRoot extends Model
{
    use HasEventProducer, HasEventSourced;

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