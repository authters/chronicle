<?php

namespace Authters\Chronicle\Projection;

use Authters\Chronicle\Support\Contracts\Projection\Publisher\Publisher;

class ProjectorRunner
{
    /**
     * @var ProjectorLock
     */
    private $lock;

    /**
     * @var ProjectorMutable
     */
    private $mutable;

    /**
     * @var ProjectorOptions
     */
    private $options;

    /**
     * @var Publisher
     */
    private $publisher;

    public function __construct(ProjectorLock $lock,
                                ProjectorMutable $mutable,
                                ProjectorOptions $options,
                                Publisher $publisher)
    {
        $this->lock = $lock;
        $this->mutable = $mutable;
        $this->options = $options;
        $this->publisher = $publisher;
    }

    public function run(bool $keepRunning)
    {

    }
}