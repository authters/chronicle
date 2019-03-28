<?php

namespace Authters\Chronicle\Projection\Projector\Projection;

use Authters\Chronicle\Projection\Factory\PersistentProjectorOptions;

final class ProjectionProjectorOptions extends PersistentProjectorOptions
{
    public const OPTION_CACHE_SIZE = 'cache_size';
    public const DEFAULT_CACHE_SIZE = 1000;

    /**
     * @var int
     */
    public $cacheSize = 1000;

    protected function availableDefault(): array
    {
        return array_merge(
            parent::availableDefault(),
            [static::OPTION_CACHE_SIZE => static::DEFAULT_CACHE_SIZE]
        );
    }
}