<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class FetchStreamNamesEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'fetch_stream_names';
    }
}