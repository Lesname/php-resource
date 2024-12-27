<?php
declare(strict_types=1);

namespace LessResource\Set;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, T>
 * @implements ResourceSet<T>
 *
 * @template T of \LessResource\Model\ResourceModel
 */
final class ArrayResourceSet implements IteratorAggregate, ResourceSet
{
    /**
     * @param array<int, T> $resources
     * @param int<0, max> $count
     */
    public function __construct(private array $resources, private int $count)
    { }

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->resources);
    }

    /**
     * @return array<int, T>
     */
    public function jsonSerialize(): array
    {
        return $this->resources;
    }

    public function count(): int
    {
        return $this->count;
    }
}
