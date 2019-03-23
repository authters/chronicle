<?php

namespace Authters\Chronicle\Exceptions;

class ConcurrencyException extends RuntimeException
{
    public static function fromQueryErrorInfo(array $errorInfo): self
    {
        return new self(
            \sprintf(
                "Error %s. \nError-Info: %s",
                $errorInfo[0],
                $errorInfo[2]
            )
        );
    }
}