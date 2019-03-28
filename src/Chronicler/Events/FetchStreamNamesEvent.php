<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class FetchStreamNamesEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'fetch_stream_names';
    }
}