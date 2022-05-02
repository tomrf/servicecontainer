<?php

declare(strict_types=1);

class OptsOnSimple
{
    public function __construct(
        private ?Simple $simple
    ) {
    }

    public function hasSimple(): bool
    {
        return isset($this->simple);
    }
}
