<?php

namespace Authters\Chronicle\Projection\Factory;

use Authters\Chronicle\Exceptions\RuntimeException;
use Illuminate\Support\Str;

class ProjectorOptions
{
    public const OPTION_PCNTL_DISPATCH = 'trigger_pcntl_dispatch';
    public const DEFAULT_PCNTL_DISPATCH = false;

    /**
     * var bool
     */
    public $triggerPcntlSignalDispatch = false;

    public function __construct(array $options = [])
    {
        foreach ($options as $default => $option) {
            $defaultCamel = Str::camel($default);

            if (!array_key_exists($defaultCamel, $this->availableDefault())) {
                throw new RuntimeException("Default key $default not available in " . (static::class));
            }

            if(!is_int($option) || !is_bool($option)){
                throw new RuntimeException("Default option for $default key must be an integer or boolean value");
            }

            $this->{$defaultCamel} = $option;
        }
    }

    protected function availableDefault(): array
    {
        return [self::DEFAULT_PCNTL_DISPATCH => self::OPTION_PCNTL_DISPATCH];
    }

    public function __set($name, $value)
    {
        throw new RuntimeException("Set value after instantiation is forbidden");
    }
}