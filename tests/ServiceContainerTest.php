<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer\Test;

use Psr\Container\ContainerInterface;
use Tomrf\Autowire\Autowire;
use Tomrf\ServiceContainer\ServiceContainer;
use Tomrf\ServiceContainer\Test\TestClass\DepsOnSimple;
use Tomrf\ServiceContainer\Test\TestClass\HasSimpleAwareness;
use Tomrf\ServiceContainer\Test\TestClass\OptsOnSimple;
use Tomrf\ServiceContainer\Test\TestClass\Simple;

/**
 * @internal
 * @coversNothing
 */
final class ServiceContainerTest extends \PHPUnit\Framework\TestCase
{
    private static ServiceContainer $serviceContainer;

    public static function setUpBeforeClass(): void
    {
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

    public function testFulfillTraits(): void
    {
        $newContainer = new ServiceContainer(new Autowire());
        $newContainer->add(Simple::class, new Simple());
        $newContainer->add(HasSimpleAwareness::class, new HasSimpleAwareness());

        $hasSimpleAwareness = $newContainer->get(HasSimpleAwareness::class);

        static::assertNull($hasSimpleAwareness->getSimple());

        $newContainer->fulfillAwarenessTraits($hasSimpleAwareness, [
            'Tomrf\ServiceContainer\Test\TestClass\SimpleAwareTrait' => [
                'setSimple' => Simple::class,
            ],
        ]);

        static::assertInstanceOf(Simple::class, $hasSimpleAwareness->getSimple());
    }

    public function testFulfillTraitsWithCallable(): void
    {
        $newContainer = new ServiceContainer(new Autowire());
        $newContainer->add(Simple::class, new Simple());
        $newContainer->add(HasSimpleAwareness::class, new HasSimpleAwareness());

        $hasSimpleAwareness = $newContainer->get(HasSimpleAwareness::class);
        $newContainer->fulfillAwarenessTraits($hasSimpleAwareness, [
            'Tomrf\ServiceContainer\Test\TestClass\SimpleAwareTrait' => [
                'setSimple' => fn () => Simple::class,
            ],
        ]);

        static::assertInstanceOf(Simple::class, $hasSimpleAwareness->getSimple());

        $hasSimpleAwareness = $newContainer->get(HasSimpleAwareness::class);
        $newContainer->fulfillAwarenessTraits($hasSimpleAwareness, [
            'Tomrf\ServiceContainer\Test\TestClass\SimpleAwareTrait' => [
                'setSimple' => fn () => new Simple(),
            ],
        ]);

        static::assertInstanceOf(Simple::class, $hasSimpleAwareness->getSimple());
    }

    private function serviceContainer(): ServiceContainer
    {
        return static::$serviceContainer;
    }
}
