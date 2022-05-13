<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use DepsOnSimple;
use OptsOnSimple;
use RandomNumber;
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

        require_once 'classes/RandomNumber.php';
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

    public function testServiceFactoryWithOptionalDependenciesNotMet(): void
    {
        $serviceContainer = new ServiceContainer(new Autowire());

        $factory = new ServiceFactory(OptsOnSimple::class);
        $serviceContainer->add(OptsOnSimple::class, $factory);

        $optsOnSimple = $serviceContainer->get(OptsOnSimple::class);
        static::assertInstanceOf(OptsOnSimple::class, $optsOnSimple);
        static::assertFalse($optsOnSimple->hasSimple());
    }

    public function testServiceFactoryWithOptionalDependenciesMet(): void
    {
        $serviceContainer = new ServiceContainer(new Autowire());

        $factory = new ServiceFactory(OptsOnSimple::class);
        $serviceContainer->add(OptsOnSimple::class, $factory);
        $serviceContainer->add(Simple::class, new Simple());

        $optsOnSimple = $serviceContainer->get(OptsOnSimple::class);
        static::assertInstanceOf(OptsOnSimple::class, $optsOnSimple);
        static::assertTrue($optsOnSimple->hasSimple());
    }

    public function testServiceFactoryBehavesLikeFactory(): void
    {
        $serviceContainer = new ServiceContainer(new Autowire());
        $factory = new ServiceFactory(RandomNumber::class);

        $serviceContainer->add(Simple::class, new Simple());
        $serviceContainer->add(RandomNumber::class, $factory);

        /** @var RandomNumber */
        $random = $serviceContainer->get(RandomNumber::class);

        static::assertSame($random->getNumber(), $random->getNumber());
        static::assertNotSame(
            ($serviceContainer->get(RandomNumber::class))->getNumber(),
            ($serviceContainer->get(RandomNumber::class))->getNumber(),
        );
    }

    public function testServiceSingleton(): void
    {
        $serviceContainer = new ServiceContainer(new Autowire());
        $factory = new ServiceSingleton(RandomNumber::class);

        $serviceContainer->add(Simple::class, new Simple());
        $serviceContainer->add(RandomNumber::class, $factory);

        /** @var RandomNumber */
        $random = $serviceContainer->get(RandomNumber::class);

        static::assertSame($random->getNumber(), $random->getNumber());
        static::assertSame(
            ($serviceContainer->get(RandomNumber::class))->getNumber(),
            ($serviceContainer->get(RandomNumber::class))->getNumber(),
        );
    }
}
