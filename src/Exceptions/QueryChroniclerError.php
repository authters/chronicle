<?php

namespace Authters\Chronicle\Exceptions;

use Illuminate\Database\QueryException;

class QueryChroniclerError extends RuntimeException
{
    public static function fromQueryException(QueryException $queryException): self
    {
        $errorInfo = $queryException->errorInfo;

        return new static(
            \sprintf("Error %s. \nError-Info: %s", $errorInfo[0], $errorInfo[2]),
            $queryException->getCode(),
            $queryException
        );
    }
}