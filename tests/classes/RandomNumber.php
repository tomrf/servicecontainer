<?php

declare(strict_types=1);

class RandomNumber
{
    private int $randomNumber;

    public function __construct(
        private Simple $simple
    ) {
        $this->randomNumber = random_int(0, PHP_INT_MAX);
    }

    public function getNumber(): int
    {
        return $this->randomNumber;
    }

    public function getSimple(): Simple
    {
        return $this->simple;
    }
}
