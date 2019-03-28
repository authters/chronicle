<?php

namespace Authters\Chronicle\Aggregate\Model;

use Authters\Chronicle\Aggregate\Concerns\HasEventApplier;
use Authters\Chronicle\Aggregate\Concerns\HasEventProducer;
use Authters\Chronicle\Aggregate\Concerns\HasEventSourced;
use Illuminate\Database\Eloquent\Model;

abstract class AggregateModelRoot extends Model
{
    use HasEventProducer, HasEventSourced, HasEventApplier;
}