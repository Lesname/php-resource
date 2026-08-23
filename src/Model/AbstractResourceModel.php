<?php

declare(strict_types=1);

namespace LesResource\Model;

use Override;
use LesValueObject\Composite\ForeignReference;
use LesValueObject\String\Format\Resource\Type;
use LesValueObject\String\Format\Resource\Identifier;

/**
 * @psalm-immutable
 */
abstract class AbstractResourceModel implements ResourceModel
{
    public function __construct(
        #[Override]
        public readonly Identifier $id,
        #[Override]
        public readonly Type $type,
    ) {}

    /**
     * @psalm-mutation-free
     */
    #[Override]
    public function asReference(): ForeignReference
    {
        return new ForeignReference($this->type, $this->id);
    }
}
