<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Publisher;

interface PublisherDecorator extends Publisher
{
    public function getInnerPublisher(): Publisher;
}