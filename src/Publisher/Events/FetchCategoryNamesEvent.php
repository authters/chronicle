<?php

namespace Authters\Chronicle\Publisher\Events;

use Authters\Chronicle\Support\Publisher\AbstractPublisherNamedEvent;

class FetchCategoryNamesEvent extends AbstractPublisherNamedEvent
{
    public function name(): string
    {
        return 'fetch_category_names';
    }
}