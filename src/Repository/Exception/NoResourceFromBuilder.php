<?php
declare(strict_types=1);

namespace LesResource\Repository\Exception;

use LesResource\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class NoResourceFromBuilder extends AbstractException implements NoResource
{
}
