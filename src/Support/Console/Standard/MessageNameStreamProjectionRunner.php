<?php

namespace Authters\Chronicle\Support\Console\Standard;

use Authters\Chronicle\Support\Projection\InternalProjectionName;
use Exception;
use Prooph\Common\Messaging\Message;

class MessageNameStreamProjectionRunner extends StreamProjectionRunner
{
    /**
     * @var string
     */
    protected $signature = 'projector:message_name-runner {--keep_running= : Run process in background}';

    /**
     * @var string
     */
    protected $description = 'Message name runner projector';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->manager
            ->createProjection('$by_message_name')
            ->fromAll()
            ->whenAny(function (array $state, Message $event): void {
                $messageName = InternalProjectionName::fromMessageName($event);
                if ($messageName->isValid()) {
                    $this->linkTo($messageName, $event);
                }
            })
            ->run($this->keepRunning());
    }
}