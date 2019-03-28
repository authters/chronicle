<?php

namespace Authters\Chronicle\Aggregate;

use Authters\Chronicle\Aggregate\Concerns\HasEventApplier;
use Authters\Chronicle\Aggregate\Concerns\HasEventProducer;
use Authters\Chronicle\Aggregate\Concerns\HasEventSourced;

abstract class AggregateRoot
{
    use HasEventProducer, HasEventSourced, HasEventApplier;

    protected function __construct()
    {
    }
}