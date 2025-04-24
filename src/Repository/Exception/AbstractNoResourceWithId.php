<?php
declare(strict_types=1);

namespace LesResource\Repository\Exception;

use LesResource\Exception\AbstractException;
use LesValueObject\String\Format\Resource\Identifier;

/**
 * @psalm-immutable
 */
abstract class AbstractNoResourceWithId extends AbstractException implements NoResource
{
    final public function __construct(public Identifier $id)
    {
        parent::__construct("No resource with id {$id}");
    }
}
