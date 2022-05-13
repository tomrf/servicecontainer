<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

class ServiceSingleton extends ServiceFactory
{
    protected object $instance;

    public function __construct(
        protected string $class
    ) {
    }

    public function make(...$args): object
    {
        if (!isset($this->instance)) {
            $this->instance = new $this->class(...$args);
        }

        return $this->instance;
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
