<?php

namespace Tomrf\ServiceContainer;

class ServiceFactory {
    public function __construct(
        protected string $class
    ) {
    }

    public function make(...$args): object
    {
        return new $this->class(...$args);
    }

    public function getClass(): string
    {
        return $this->class;
    }
}