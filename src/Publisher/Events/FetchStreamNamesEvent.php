<?php

namespace Authters\Chronicle\Publisher\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class FetchStreamNamesEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'fetch_stream_names';
    }
}