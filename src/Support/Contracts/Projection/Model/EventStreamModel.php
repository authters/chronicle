<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Model;

interface EventStreamModel
{
    public function getId(): int;

    public function realStreamName(): string;

    public function streamName(): string;

    public function metadata(): string;

    public function category(): string;
}