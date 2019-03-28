<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class FetchCategoryNamesEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'fetch_category_names';
    }
}