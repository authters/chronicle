<?php

namespace Authters\Chronicle\Exceptions;

use Authters\Chronicle\Support\Contracts\Exception\ChronicleException;

class RuntimeException extends \RuntimeException implements ChronicleException
{
}