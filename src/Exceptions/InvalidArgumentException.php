<?php

namespace Authters\Chronicle\Exceptions;

use Authters\Chronicle\Support\Contracts\Exception\ChronicleException;

class InvalidArgumentException extends \InvalidArgumentException implements ChronicleException
{

}