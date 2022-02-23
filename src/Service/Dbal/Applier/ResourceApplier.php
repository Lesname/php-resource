<?php
declare(strict_types=1);

namespace LessResource\Service\Dbal\Applier;

use LessDatabase\Query\Builder\Applier\Applier;

interface ResourceApplier extends Applier
{
    public function getTableName(): string;

    public function getTableAlias(): string;
}
