<?php
declare(strict_types=1);

namespace LessResource\Repository\Exception;

use LessResource\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class NoResourceFromBuilder extends AbstractException implements NoResource
{
}
