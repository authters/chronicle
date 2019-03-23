<?php

namespace Authters\Chronicle\Support\Contracts\Metadata;

interface MetadataMatcher
{
    /**
     * @return callable|array
     */
    public function data();
}