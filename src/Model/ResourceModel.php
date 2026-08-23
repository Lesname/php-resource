<?php

declare(strict_types=1);

namespace LesResource\Model;

use LesValueObject\Composite\ForeignReference;
use LesValueObject\Composite\CompositeValueObject;
use LesValueObject\String\Format\Resource\Identifier;
use LesValueObject\String\Format\Resource\Type;

/**
 * @psalm-immutable
 */
interface ResourceModel extends CompositeValueObject
{
    public Identifier $id { get; }
    public Type $type { get; }

    /**
     * @psalm-mutation-free
     */
    public function asReference(): ForeignReference;
}
