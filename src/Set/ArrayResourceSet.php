<?php

declare(strict_types=1);

namespace LesResource\Set;

use Override;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, T>
 * @implements ResourceSet<T>
 *
 * @template T of \LesResource\Model\ResourceModel
 *
 * @psalm-suppress ImmutableDependency
 */
final class ArrayResourceSet implements IteratorAggregate, ResourceSet
{
    /**
     * @param array<int, T> $resources
     * @param int<0, max> $count
     *
     * @psalm-mutation-free
     */
    public function __construct(
        private readonly array $resources,
        private readonly int $count,
    ) {}

    /**
     * @return Traversable<int, T>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->resources);
    }

    /**
     * @return array<int, T>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->resources;
    }

    #[Override]
    public function count(): int
    {
        return $this->count;
    }
}
