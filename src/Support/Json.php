<?php

namespace Authters\Chronicle\Support;

class Json
{
    public static function encode($value): string
    {
        $flags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;

        $json = \json_encode($value, $flags);

        if (JSON_ERROR_NONE !== $error = \json_last_error()) {
            throw new \RuntimeException(\json_last_error_msg(), $error);
        }

        return $json;
    }

    public static function decode(string $json)
    {
        $data = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING);

        if (JSON_ERROR_NONE !== $error = \json_last_error()) {
            throw new \RuntimeException(\json_last_error_msg(), $error);
        }

        return $data;
    }
}