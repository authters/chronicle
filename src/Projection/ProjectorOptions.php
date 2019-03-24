<?php

namespace Authters\Chronicle\Projection;

class ProjectorOptions
{
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

    /**
     * @var int
     */
    public $cacheSize = 1000; // only use by default projection

    /**
     * var bool
     */
    public $triggerPcntlSignalDispatch = false;
}