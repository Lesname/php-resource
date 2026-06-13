<?php

declare(strict_types=1);

namespace LesResource\Repository\Dbal\Applier;

use LesDatabase\Query\Builder\Applier\Applier;

/**
 * @psalm-mutable
 */
interface ResourceApplier extends Applier
{
    /**
     * @psalm-mutation-free
     */
    public function getTableName(): string;

    /**
     * @psalm-mutation-free
     */
    public function getTableAlias(): string;
}
