<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Model;

use Authters\Chronicle\Projection\ProjectionStatus;

interface ProjectionModel
{
    public function getName(): string;

    public function getPosition(): string;

    public function getState(): string;

    public function getStatus(): ProjectionStatus;

    public function getLockedUntil(): ?string;
}