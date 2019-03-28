<?php

namespace Authters\Chronicle\Chronicler\Events;

use Authters\Chronicle\Support\Chronicler\AbstractChroniclerNamedEvent;

class FetchCategoryNamesEvent extends AbstractChroniclerNamedEvent
{
    public function name(): string
    {
        return 'fetch_category_names';
    }
}