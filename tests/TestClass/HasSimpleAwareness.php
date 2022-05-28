<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer\Test\TestClass;

/**
 * @internal
 */
final class HasSimpleAwareness
{
    use SimpleAwareTrait;

    public function getSimple(): ?Simple
    {
        return $this->simple;
    }
}
