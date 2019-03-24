<?php

namespace Authters\Chronicle\Publisher\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class UpdateStreamMetadataEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'update_stream_metadata';
    }
}