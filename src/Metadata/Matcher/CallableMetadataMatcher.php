<?php

namespace Authters\Chronicle\Metadata\Matcher;

use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;

class CallableMetadataMatcher implements MetadataMatcher
{
    /**
     * @var callable
     */
    private $metadataMatcher;

    public function __construct(callable $metadataMatcher)
    {
        $this->metadataMatcher = $metadataMatcher;
    }

    public function data(): callable
    {
        return $this->metadataMatcher;
    }
}