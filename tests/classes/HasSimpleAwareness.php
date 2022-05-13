<?php

declare(strict_types=1);

class HasSimpleAwareness
{
    use SimpleAwareTrait;

    public function getSimple(): ?Simple
    {
        return $this->simple;
    }
}
