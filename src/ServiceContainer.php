<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tomrf\Autowire\Autowire;
use Tomrf\Autowire\AutowireException;
use Tomrf\Autowire\NotFoundException;

/**
 * ServiceContainer.
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
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

        $resolved = $this->resolve($id);

        if (\is_object($resolved)) {
            $this->fulfillAwarenessTraits($resolved);
        }

        return $resolved;
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
     * Fulfill an objects awereness traits.
     *
     * @throws NotFoundException
     */
    public function fulfillAwarenessTraits(mixed $object): void
    {
        // LoggerAwareInterface
        if ($this->has(LoggerInterface::class)) {
            if ($object instanceof LoggerAwareInterface) {
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
        $object = $this->container[$id];

        if (!\is_object($object)) {
            return $object;
        }

        if (!\is_callable($object) && !$object instanceof ServiceFactory) {
            return $object;
        }

        $dependencies = $this->autowire->resolveDependencies(
            ($object instanceof ServiceFactory) ? $object->getClass() : $object,
            '__construct',
            [$this]
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
