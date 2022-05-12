<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use DepsOnSimple;
use Simple;
use Tomrf\Autowire\Autowire;

/**
 * @internal
 * @coversNothing
 */
final class ServiceFactoryTest extends \PHPUnit\Framework\TestCase
{
    private static ServiceContainer $serviceContainer;

    public static function setUpBeforeClass(): void
    {
        require_once 'classes/Simple.php';

        require_once 'classes/DepsOnSimple.php';

        require_once 'classes/OptsOnSimple.php';

        static::$serviceContainer = new ServiceContainer(
            new Autowire()
        );
    }

    public function testServiceFactory(): void
    {
        $factory = new ServiceFactory(Simple::class);
        $instance = $factory->make();
        static::assertInstanceOf(Simple::class, $instance);
    }

    public function testServiceFactoryWithDependencies(): void
    {
        $serviceContainer = new ServiceContainer(new Autowire());
        $serviceContainer->add(Simple::class, new Simple());

        $factory = new ServiceFactory(DepsOnSimple::class);
        $serviceContainer->add(DepsOnSimple::class, $factory);

        $depsOnSimple = $serviceContainer->get(DepsOnSimple::class);

        static::assertInstanceOf(DepsOnSimple::class, $depsOnSimple);
    }

    private function serviceContainer(): ServiceContainer
    {
        return static::$serviceContainer;
    }
}
