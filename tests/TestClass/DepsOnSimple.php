<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer\Test\TestClass;

/**
 * @internal
 */
final class DepsOnSimple
{
    public function __construct(
        private Simple $simple
    ) {
    }
}
