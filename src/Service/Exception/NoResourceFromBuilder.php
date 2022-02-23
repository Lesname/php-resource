<?php
declare(strict_types=1);

namespace LessResource\Service\Exception;

use LessResource\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class NoResourceFromBuilder extends AbstractException implements NoResource
{
}
