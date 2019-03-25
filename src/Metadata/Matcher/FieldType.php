<?php

namespace Authters\Chronicle\Metadata\Matcher;

use MabeEnum\Enum;

/**
 * @method static FieldType METADATA()
 * @method static FieldType MESSAGE_PROPERTY()
 */
final class FieldType extends Enum
{
    public const METADATA = 0;
    public const MESSAGE_PROPERTY = 1;
}