<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use DepsOnSimple;
use OptsOnSimple;
use Psr\Container\ContainerInterface;
use Simple;
use Tomrf\Autowire\Autowire;

/**
 * @internal
 * @coversNothing
 */
final class ServiceContainerTest extends \PHPUnit\Framework\TestCase
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

    public function testServiceContainerIsInstanceOfServiceContainer(): void
    {
        static::assertIsObject(static::$serviceContainer);
        static::assertInstanceOf(ServiceContainer::class, static::$serviceContainer);
        static::assertInstanceOf(ContainerInterface::class, static::$serviceContainer);
    }

    public function testServiceContainerString(): void
    {
        $this->serviceContainer()->add('string', 'lorem ipsum');
        static::assertSame('lorem ipsum', $this->serviceContainer()->get('string'));
    }

    public function testServiceContainerBool(): void
    {
        $this->serviceContainer()->add('bool', true);
        static::assertTrue($this->serviceContainer()->get('bool'));
    }

    public function testServiceContainerSimpleClassInstance(): void
    {
        $this->serviceContainer()->add(Simple::class, new Simple());
        static::assertInstanceOf(Simple::class, $this->serviceContainer()->get(Simple::class));
    }

    public function testServiceContainerInstanceWithDependency(): void
    {
        $this->serviceContainer()->add(DepsOnSimple::class, fn (Simple $simple) => new DepsOnSimple($simple));
        static::assertInstanceOf(DepsOnSimple::class, $this->serviceContainer()->get(DepsOnSimple::class));
    }

    public function testDependencyResolutionIsLazy(): void
    {
        $newContainer = new ServiceContainer(new Autowire());
        $newContainer->add(DepsOnSimple::class, fn (Simple $simple) => new DepsOnSimple($simple));
        $newContainer->add(Simple::class, fn () => new Simple());
        static::assertInstanceOf(DepsOnSimple::class, $newContainer->get(DepsOnSimple::class));
        static::assertInstanceOf(Simple::class, $newContainer->get(Simple::class));
    }

    public function testDependencyResolutionForOptionalDependency(): void
    {
        $newContainer = new ServiceContainer(new Autowire());
        $newContainer->add(OptsOnSimple::class, fn (?Simple $simple) => new OptsOnSimple($simple));
        static::assertInstanceOf(OptsOnSimple::class, $newContainer->get(OptsOnSimple::class));
        static::assertFalse($newContainer->get(OptsOnSimple::class)->hasSimple());

        $newContainer = new ServiceContainer(new Autowire());
        $newContainer->add(Simple::class, fn () => new Simple());
        $newContainer->add(OptsOnSimple::class, fn (?Simple $simple) => new OptsOnSimple($simple));
        static::assertInstanceOf(OptsOnSimple::class, $newContainer->get(OptsOnSimple::class));
        static::assertTrue($newContainer->get(OptsOnSimple::class)->hasSimple());
    }

    private function serviceContainer(): ServiceContainer
    {
        return static::$serviceContainer;
    }
}
