<?php
declare(strict_types=1);

namespace LesResource\Set;

use Countable;
use JsonSerializable;
use Traversable;

/**
 * @extends Traversable<int, T>
 *
 * @template T of \LesResource\Model\ResourceModel
 */
interface ResourceSet extends Traversable, JsonSerializable, Countable
{
}
