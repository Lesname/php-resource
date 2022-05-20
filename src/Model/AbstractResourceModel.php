<?php
declare(strict_types=1);

namespace LessResource\Model;

use LessValueObject\Composite\AbstractCompositeValueObject;
use LessValueObject\Composite\ForeignReference;
use LessValueObject\String\Format\Resource\Identifier;
use LessValueObject\String\Format\Resource\Type;

/**
 * @psalm-immutable
 *
 * @deprecated AbstractResourceModel will be dropped
 *
 * @psalm-suppress DeprecatedClass
 */
abstract class AbstractResourceModel extends AbstractCompositeValueObject implements ResourceModel
{
    public function __construct(
        public Identifier $id,
        public Type $type,
    ) {}

    public function getForeignReference(): ForeignReference
    {
        return new ForeignReference($this->type, $this->id);
    }
}
