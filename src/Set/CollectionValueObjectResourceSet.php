<?php
declare(strict_types=1);

namespace LessResource\Set;

use Traversable;
use IteratorAggregate;
use LessValueObject\Collection\CollectionValueObject;

/**
 * @implements IteratorAggregate<int, T>
 * @implements ResourceSet<T>
 *
 * @template T of \LessResource\Model\ResourceModel
 */
final class CollectionValueObjectResourceSet implements IteratorAggregate, ResourceSet
{
    /**
     * @param CollectionValueObject<T> $collection
     * @param int<0, max> $count
     */
    public function __construct(private CollectionValueObject $collection, private int $count)
    {}

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return $this->collection;
    }

    /**
     * @return CollectionValueObject<T>
     */
    public function jsonSerialize(): CollectionValueObject
    {
        return $this->collection;
    }

    public function count(): int
    {
        return $this->count;
    }
}
