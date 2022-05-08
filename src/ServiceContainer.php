<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tomrf\Autowire\Autowire;
use Tomrf\Autowire\AutowireException;
use Tomrf\Autowire\NotFoundException;

class ServiceContainer extends \Tomrf\Autowire\Container implements \Psr\Container\ContainerInterface
{
    /**
     * @var array<string,mixed>
     */
    protected array $container = [];

    public function __construct(
        private Autowire $autowire
    ) {
    }

    /**
     * Return a service.
     *
     * @throws NotFoundException
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException('Not found: '.$id);
        }

        if (\is_callable($this->container[$id])) {
            $this->container[$id] = $this->resolve($id);
        }

        return $this->container[$id];
    }

    /**
     * Add a service. Fails if the service id has already been assigned.
     *
     * @throws RuntimeException
     */
    public function add(string $id, mixed $value): void
    {
        if (false !== $this->has($id)) {
            throw new RuntimeException(sprintf(
                'Unable to add to container, container already has "%s"',
                $id
            ));
        }

        $this->set($id, $value);
    }

    /**
     * Remove a service.
     */
    public function remove(string $id): void
    {
        if ($this->has($id)) {
            unset($this->container[$id]);
        }
    }

    /**
     * Fulfill an objects awereness traits.
     *
     * @throws NotFoundException
     */
    public function fulfillAwaressTraits(mixed $object): void
    {
        // LoggerAwareInterface
        if ($this->has(LoggerInterface::class)) {
            if ($object instanceof LoggerAwareInterface
            && method_exists($object, 'setLogger')) {
                /** @var LoggerInterface */
                $logger = $this->get(LoggerInterface::class);
                $object->setLogger($logger);
            }
        }
    }

    /**
     * Return Autowire instance.
     */
    public function autowire(): Autowire
    {
        return $this->autowire;
    }

    /**
     * Resolve class constructor dependencies.
     *
     * @throws AutowireException
     */
    private function resolve(string $id): mixed
    {
        $item = $this->container[$id];

        if (!\is_callable($item) || !\is_object($item)) {
            return $item;
        }

        $dependencies = $this->autowire->resolveDependencies(
            $item,
            '__construct',
            [$this]
        );

        $instance = $item(
            ...$dependencies,
        );

        $this->fulfillAwaressTraits($instance);

        return $instance;
    }
}
