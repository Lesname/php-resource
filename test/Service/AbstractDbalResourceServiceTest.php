<?php
declare(strict_types=1);

namespace LessResourceTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use LessHydrator\Hydrator;
use LessResource\Service\AbstractDbalResourceService;
use LessResource\Service\Dbal\Applier\ResourceApplier;
use LessResource\Service\Exception\AbstractNoResourceWithId;
use LessValueObject\String\Format\Resource\Identifier;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LessResource\Service\AbstractDbalResourceService
 */
final class AbstractDbalResourceServiceTest extends TestCase
{
    public function testExists(): void
    {
        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('getTableName')
            ->willReturn('table');

        $applier
            ->expects(self::exactly(2))
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', 't');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('t.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('select')
            ->with('count(*)')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('1');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);

        $mock = $this->getMockForAbstractClass(
            AbstractDbalResourceService::class,
            [$connection, $hydrator],
        );
        $mock
            ->method('getResourceApplier')
            ->willReturn($applier);

        self::assertTrue($mock->exists($id));
    }

    public function testNotExists(): void
    {
        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('getTableName')
            ->willReturn('table');

        $applier
            ->expects(self::exactly(2))
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', 't');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('t.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('select')
            ->with('count(*)')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('0');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);

        $mock = $this->getMockForAbstractClass(
            AbstractDbalResourceService::class,
            [$connection, $hydrator],
        );
        $mock
            ->method('getResourceApplier')
            ->willReturn($applier);

        self::assertFalse($mock->exists($id));
    }

    public function testGetCurrentVersion(): void
    {
        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('getTableName')
            ->willReturn('table');

        $applier
            ->expects(self::exactly(2))
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', 't');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('t.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('select')
            ->with('version')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('2');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);

        $mock = $this->getMockForAbstractClass(
            AbstractDbalResourceService::class,
            [$connection, $hydrator],
        );
        $mock
            ->method('getResourceApplier')
            ->willReturn($applier);

        self::assertSame(2, $mock->getCurrentVersion($id));
    }

    public function testGetCurrentVersionUnknown(): void
    {
        $this->expectException(AbstractNoResourceWithId::class);

        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $e = new class ($id) extends AbstractNoResourceWithId {
        };

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('getTableName')
            ->willReturn('table');

        $applier
            ->expects(self::exactly(2))
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', 't');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('t.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('select')
            ->with('version')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn(false);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);

        $mock = $this->getMockForAbstractClass(
            AbstractDbalResourceService::class,
            [$connection, $hydrator],
        );
        $mock
            ->method('getResourceApplier')
            ->willReturn($applier);

        $mock
            ->expects(self::once())
            ->method('getNoResourceWithIdClass')
            ->willReturn($e::class);

        $mock->getCurrentVersion($id);
    }
}
