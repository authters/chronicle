<?php

namespace Authters\Chronicle\Exceptions;

use Authters\Chronicle\Stream\StreamName;

class StreamAlreadyExists extends RuntimeException
{
    public static function with(StreamName $streamName): self
    {
        return new static("Stream name {$streamName} already exists");
    }
}