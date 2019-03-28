<?php

namespace Authters\Chronicle\Support\Contracts\Projection\Chronicler;

interface ChroniclerDecorator extends Chronicler
{
    public function getInnerChronicler(): Chronicler;
}