<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
