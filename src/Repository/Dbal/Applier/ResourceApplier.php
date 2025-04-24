<?php
declare(strict_types=1);

namespace LesResource\Repository\Dbal\Applier;

use LesDatabase\Query\Builder\Applier\Applier;

interface ResourceApplier extends Applier
{
    public function getTableName(): string;

    public function getTableAlias(): string;
}
