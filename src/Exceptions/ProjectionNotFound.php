<?php

namespace Authters\Chronicle\Exceptions;

use Authters\Chronicle\Stream\StreamName;

class ProjectionNotFound extends RuntimeException
{
    public static function with(StreamName $streamName): self
    {
        return new static("Projection {$streamName} not found");
    }
}