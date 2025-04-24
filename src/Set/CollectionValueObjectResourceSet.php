<?php
declare(strict_types=1);

namespace LesResource\Set;

use Override;
use Traversable;
use IteratorAggregate;
use LesValueObject\Collection\CollectionValueObject;

/**
 * @implements IteratorAggregate<int, T>
 * @implements ResourceSet<T>
 *
 * @template T of \LesResource\Model\ResourceModel
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
    #[Override]
    public function getIterator(): Traversable
    {
        return $this->collection;
    }

    /**
     * @return CollectionValueObject<T>
     */
    #[Override]
    public function jsonSerialize(): CollectionValueObject
    {
        return $this->collection;
    }

    #[Override]
    public function count(): int
    {
        return $this->count;
    }
}
