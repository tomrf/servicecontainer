<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer\Test\TestClass;

/**
 * @internal
 */
final class OptsOnSimple
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
