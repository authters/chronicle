<?php

namespace Authters\Chronicle\Projection;

class ProjectorOptions
{
    /**
     * @var int
     */
    public $persistBlockSize;

    /**
     * @var int lock timeout in milliseconds
     */
    public $lockTimeoutMs;

    /**
     * @var int
     */
    public $sleep;

    /**
     * @var int
     */
    public $updateLockThreshold;

    /**
     * var bool
     */
    public $triggerPcntlSignalDispatch = false;
}