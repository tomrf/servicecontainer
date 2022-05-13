<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

class ServiceFactory
{
    public function __construct(
        protected string $class
    ) {
    }

    public function make(object|null ...$args): object
    {
        return new $this->class(...$args);
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
