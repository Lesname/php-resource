<?php
declare(strict_types=1);

namespace LessResource\Service\Exception;

use LessResource\Exception\AbstractException;
use LessValueObject\String\Format\Resource\Identifier;

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
