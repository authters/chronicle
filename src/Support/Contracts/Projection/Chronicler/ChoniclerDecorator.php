<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Chronicler;

interface ChoniclerDecorator extends Chronicler
{
    public function getInnerPublisher(): Chronicler;
}