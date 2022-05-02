<?php

declare(strict_types=1);

namespace Tomrf\ServiceContainer;

use RuntimeException;
use Tomrf\Autowire\Autowire;
use Tomrf\Autowire\AutowireException;

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
     * @throws RuntimeException
     */
    public function add(string $id, mixed $value): void
    {
        if (false !== $this->has($id)) {
            throw new RuntimeException('Unable to add to container, container already has '.$id);
        }

        $this->set($id, $value);
    }

    public function remove(string $id): void
    {
        if ($this->has($id)) {
            unset($this->container[$id]);
        }
    }

    /**
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

        return $item(
            ...$dependencies,
        );
    }
}
