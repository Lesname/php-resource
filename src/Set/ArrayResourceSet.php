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
 */
final class ArrayResourceSet implements IteratorAggregate, ResourceSet
{
    /**
     * @param array<int, T> $resources
     * @param int<0, max> $count
     */
    public function __construct(private array $resources, private int $count)
    {}

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
