<?php

declare(strict_types=1);

namespace LesResource\Set;

use Override;
use Exception;
use Traversable;
use EmptyIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, T>
 * @implements ResourceSet<T>
 *
 * @template T of \LesResource\Model\ResourceModel
 *
 * @psalm-immutable
 */
final class EmptyResourceSet implements IteratorAggregate, ResourceSet
{
    /**
     * @psalm-pure
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new EmptyIterator();
    }

    /**
     * @psalm-pure
     */
    #[Override]
    public function count(): int
    {
        return 0;
    }

    /**
     * @psalm-pure
     */
    #[Override]
    public function jsonSerialize(): mixed
    {
        return [];
    }
}
