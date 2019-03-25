<?php

namespace Authters\Chronicle\Metadata\Matcher;


use MabeEnum\Enum;

/**
 * @method static Operator EQUALS()
 * @method static Operator GREATER_THAN()
 * @method static Operator GREATER_THAN_EQUALS()
 * @method static Operator IN()
 * @method static Operator LOWER_THAN()
 * @method static Operator LOWER_THAN_EQUALS()
 * @method static Operator NOT_EQUALS()
 * @method static Operator NOT_IN()
 * @method static Operator REGEX()
 */
class Operator extends Enum
{
    public const EQUALS = '=';
    public const GREATER_THAN = '>';
    public const GREATER_THAN_EQUALS = '>=';
    public const IN = 'in';
    public const LOWER_THAN = '<';
    public const LOWER_THAN_EQUALS = '<=';
    public const NOT_EQUALS = '!=';
    public const NOT_IN = 'nin';
    public const REGEX = 'regex';
}