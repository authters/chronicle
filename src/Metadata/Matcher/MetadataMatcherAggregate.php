<?php

namespace Authters\Chronicle\Metadata\Matcher;

use Authters\Chronicle\Support\Contracts\Metadata\MetadataMatcher;

final class MetadataMatcherAggregate implements MetadataMatcher
{
    /**
     * @var ExpresionFactory[]
     */
    private $metadataFactories;

    public function __construct(array $metadataFactories = [])
    {
        $this->metadataFactories = $metadataFactories;
    }

    public function data(): array
    {
        return $this->metadataFactories;
    }
}