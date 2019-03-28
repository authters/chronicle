<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class UpdateStreamMetadataEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'update_stream_metadata';
    }
}