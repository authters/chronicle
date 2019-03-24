<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Projector;

use Prooph\Common\Messaging\Message;

interface ProjectionProjector extends PersistentProjector
{
    /**
     * @param Message $event
     * @throws \Throwable
     */
    public function emit(Message $event): void;

    /**
     * @param string $streamName
     * @param Message $event
     * @throws \Throwable
     */
    public function linkTo(string $streamName, Message $event): void;
}