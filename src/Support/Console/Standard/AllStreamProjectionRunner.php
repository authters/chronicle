<?php

namespace Authters\Chronicle\Support\Console\Standard;

use Exception;
use Prooph\Common\Messaging\Message;

class AllStreamProjectionRunner extends StreamProjectionRunner
{
    /**
     * @var string
     */
    protected $signature = 'projector:all-runner {--keep_running= : Run process in background}';

    /**
     * @var string
     */
    protected $description = 'All runner projector';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->manager
            ->createProjection('$all')
            ->fromAll()
            ->whenAny(function (array $state, Message $event): void {
                $this->emit($event);
            })
            ->run($this->keepRunning());
    }
}