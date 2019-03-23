<?php

namespace Authters\Chronicle\Support\Contracts\Metadata;

use Prooph\Common\Messaging\Message;

interface MetadataEnricher
{
    public function enrich(Message $message): Message;
}