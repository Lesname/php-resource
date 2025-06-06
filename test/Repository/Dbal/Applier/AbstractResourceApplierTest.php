<?php
declare(strict_types=1);

namespace LesResourceTest\Repository\Dbal\Applier;

use Doctrine\DBAL\Query\QueryBuilder;
use LesResource\Repository\Dbal\Applier\AbstractResourceApplier;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesResource\Repository\Dbal\Applier\AbstractResourceApplier
 */
final class AbstractResourceApplierTest extends TestCase
{
    public function testApply(): void
    {
        $class = new class extends AbstractResourceApplier {
            protected function getFields(): array
            {
                return [
                    'id' => 't.id',
                    'attributes' => [
                        'name' => 't.name',
                    ],
                ];
            }

            public function getTableName(): string
            {
                return 'table';
            }

            public function getTableAlias(): string
            {
                return 't';
            }
        };

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', '`t`')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('addSelect')
            ->with(
                "t.id as 'id'",
                "t.name as 'attributes.name'",
            )
            ->willReturn($builder);

        $class->apply($builder);
    }
}
