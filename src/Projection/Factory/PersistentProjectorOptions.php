<?php

namespace Authters\Chronicle\Projection\Factory;

class PersistentProjectorOptions extends ProjectorOptions
{
    public const OPTION_SLEEP = 'sleep';
    public const OPTION_PERSIST_BLOCK_SIZE = 'persist_block_size';
    public const OPTION_LOCK_TIMEOUT_MS = 'lock_timeout_ms';
    public const OPTION_UPDATE_LOCK_THRESHOLD = 'update_lock_threshold';

    public const DEFAULT_SLEEP = 100000;
    public const DEFAULT_PERSIST_BLOCK_SIZE = 1000;
    public const DEFAULT_LOCK_TIMEOUT_MS = 1000;
    public const DEFAULT_UPDATE_LOCK_THRESHOLD = 0;

    /**
     * @var int
     */
    public $persistBlockSize = 1000;

    /**
     * @var int
     */
    public $lockTimeoutMs = 1000;

    /**
     * @var int
     */
    public $sleep = 100000;

    /**
     * @var int
     */
    public $updateLockThreshold = 0;

    protected function availableDefault(): array
    {
        return array_merge(
            parent::availableDefault(),
            [
                static::OPTION_SLEEP => static::DEFAULT_SLEEP,
                static::OPTION_PERSIST_BLOCK_SIZE => static::DEFAULT_PERSIST_BLOCK_SIZE,
                static::OPTION_LOCK_TIMEOUT_MS => static::DEFAULT_LOCK_TIMEOUT_MS,
                static::OPTION_UPDATE_LOCK_THRESHOLD => static::DEFAULT_UPDATE_LOCK_THRESHOLD
            ]
        );
    }
}