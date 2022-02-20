<?php
declare(strict_types=1);

namespace LessResource\Set;

use Countable;
use JsonSerializable;
use Traversable;

/**
 * @extends Traversable<int, T>
 *
 * @template T of \LessResource\Model\ResourceModel
 */
interface ResourceSet extends Traversable, JsonSerializable, Countable
{
}
