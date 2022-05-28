<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer\Test\TestClass;

/**
 * @internal
 */
trait SimpleAwareTrait
{
    private ?Simple $simple = null;

    public function setSimple(Simple $simple): void
    {
        $this->simple = $simple;
    }
}
