<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Strategy;

interface QueryHint
{
    public function indexName(): string;
}