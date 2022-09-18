<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use RuntimeException;
use Tomrf\Autowire\Autowire;
use Tomrf\Autowire\AutowireException;

/**
 * ServiceContainer.
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class ServiceContainer implements \Psr\Container\ContainerInterface
{
    /**
     * @var array<string,mixed>
     */
    protected array $container = [];

    public function __construct(
        private Autowire $autowire
    ) {
    }

    public function has(string $id): bool
    {
        return isset($this->container[$id]);
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

        return $this->resolve($id);
    }

    /**
     * Add a service. Fails if the service id has already been assigned.
     *
     * @throws RuntimeException
     */
    public function add(string $id, mixed $value): void
    {
        if (true === $this->has($id)) {
            throw new RuntimeException(sprintf(
                'Unable to add to container, container already has "%s"',
                $id
            ));
        }

        $this->set($id, $value);
    }

    public function set(string $id, mixed $value): void
    {
        $this->container[$id] = $value;
    }

    /**
     * Remove a service.
     */
    public function remove(string $id): void
    {
        if (true === $this->has($id)) {
            unset($this->container[$id]);
        }
    }

    /**
     * Fulfill the objects awereness traits using the provided trait map.
     *
     * @param array<array<string,callable|string>> $traitMap
     *
     * @throws NotFoundException
     */
    public function fulfillAwarenessTraits(object $object, array $traitMap): void
    {
        $traits = class_uses($object);
        if (\is_bool($traits)) {
            return;
        }

        foreach ($traits as $trait) {
            if (isset($traitMap[$trait])) {
                $setMethod = key($traitMap[$trait]);
                $classOrCallable = $traitMap[$trait][$setMethod];

                if (\is_callable($classOrCallable)) {
                    $classOrCallable = $classOrCallable();
                }

                if (\is_string($classOrCallable)) {
                    $object->{$setMethod}($this->get($classOrCallable));

                    continue;
                }

                $object->{$setMethod}($classOrCallable);
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
        $object = $this->container[$id];

        if (!\is_object($object)) {
            return $object;
        }

        if (!\is_callable($object) && !$object instanceof ServiceFactory) {
            return $object;
        }

        $dependencies = $this->autowire->resolveDependencies(
            ($object instanceof ServiceFactory) ? $object->getClass() : $object,
            $this
        );

        if ($object instanceof ServiceFactory) {
            return $object->make(
                ...$dependencies,
            );
        }

        return $object(
            ...$dependencies,
        );
    }
}
