<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class UpdateStreamMetadataEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'update_stream_metadata';
    }
}