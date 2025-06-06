<?php
declare(strict_types=1);

namespace LesResource\Repository\Dbal\Applier;

use Override;
use Doctrine\DBAL\Query\QueryBuilder;
use LesDatabase\Query\Builder\Applier\SelectApplier;

abstract class AbstractResourceApplier implements ResourceApplier
{
    /**
     * @return array<string, string|array<string, string|array<string, string|array<string, string|array<string, string|array<string, string|array<string, string|array<string, string>>>>>>>>
     */
    abstract protected function getFields(): array;

    /**
     * @psalm-suppress MixedArgumentTypeCoercion flatten ...
     */
    #[Override]
    public function apply(QueryBuilder $builder): QueryBuilder
    {
        $builder->from("`{$this->getTableName()}`", "`{$this->getTableAlias()}`");

        $applier = SelectApplier::fromNested($this->getFields());
        $applier->apply($builder);

        return $builder;
    }
}
