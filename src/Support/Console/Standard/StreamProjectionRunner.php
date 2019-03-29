<?php

namespace Authters\Chronicle\Support\Console\Standard;

use Authters\Chronicle\Support\Contracts\Projection\ProjectionManager;
use Illuminate\Console\Command;

abstract class StreamProjectionRunner extends Command
{
    /**
     * @var ProjectionManager
     */
    protected $manager;

    public function __construct(ProjectionManager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function keepRunning(): bool
    {
        return $this->option('keep_running') ? true : false;
    }
}