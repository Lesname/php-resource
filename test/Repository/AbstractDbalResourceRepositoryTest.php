<?php
declare(strict_types=1);

namespace LesResourceTest\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use LesHydrator\Hydrator;
use LesResource\Model\ResourceModel;
use LesResource\Repository\AbstractDbalResourceRepository;
use LesResource\Repository\Dbal\Applier\ResourceApplier;
use LesResource\Repository\Exception\AbstractNoResourceWithId;
use LesValueObject\Composite\Paginate;
use LesValueObject\Number\Int\Paginate\Page;
use LesValueObject\Number\Int\Paginate\PerPage;
use LesValueObject\String\Format\Resource\Identifier;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \LesResource\Repository\AbstractDbalResourceRepository
 */
final class AbstractDbalResourceRepositoryTest extends TestCase
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
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', '`t`');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
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

        $mock = new class ($connection, $hydrator, $applier) extends AbstractDbalResourceRepository {
            public function __construct(Connection $connection, Hydrator $hydrator, private readonly ResourceApplier $applier)
            {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                throw new RuntimeException();
            }

            protected function getNoResourceWithIdClass(): string
            {
                throw new RuntimeException();
            }
        };

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
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', '`t`');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
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

        $mock = new class ($connection, $hydrator, $applier) extends AbstractDbalResourceRepository {
            public function __construct(Connection $connection, Hydrator $hydrator, private readonly ResourceApplier $applier)
            {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                throw new RuntimeException();
            }

            protected function getNoResourceWithIdClass(): string
            {
                throw new RuntimeException();
            }
        };

        self::assertFalse($mock->exists($id));
    }

    public function testGetWithId(): void
    {
        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $model = $this->createMock(ResourceModel::class);

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchAssociative')
            ->willReturn(
                [
                    'id' => '4',
                    'attributes.name' => 'bar',
                    'attributes.foo.bar' => 'baz',
                ],
            );

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('apply')
            ->with($builder);

        $applier
            ->method('getTableAlias')
            ->willReturn('t');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);
        $hydrator
            ->expects(self::once())
            ->method('hydrate')
            ->with(
                ResourceModel::class,
                [
                    'id' => '4',
                    'attributes' => [
                        'name' => 'bar',
                        'foo' => [
                            'bar' => 'baz',
                        ],
                    ],
                ],
            )
            ->willReturn($model);

        $mock = new class ($connection, $hydrator, $applier) extends AbstractDbalResourceRepository {
            public function __construct(Connection $connection, Hydrator $hydrator, private readonly ResourceApplier $applier)
            {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                return ResourceModel::class;
            }

            protected function getNoResourceWithIdClass(): string
            {
                throw new RuntimeException();
            }
        };

        self::assertSame($model, $mock->getWithId($id));
    }

    public function testGetWithIdNotFound(): void
    {
        $this->expectException(AbstractNoResourceWithId::class);

        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $e = new class ($id) extends AbstractNoResourceWithId {
        };

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('apply')
            ->with($builder);

        $applier
            ->method('getTableAlias')
            ->willReturn('t');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);

        $mock = new class ($connection, $hydrator, $applier, $e::class) extends AbstractDbalResourceRepository {
            public function __construct(
                Connection $connection,
                Hydrator $hydrator,
                private readonly ResourceApplier $applier,
                private readonly string $noResourceWithId,
            ) {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                throw new RuntimeException();
            }

            protected function getNoResourceWithIdClass(): string
            {
                return $this->noResourceWithId;
            }
        };

        $mock->getWithId($id);
    }

    public function testGetByLastActivity(): void
    {
        $model = $this->createMock(ResourceModel::class);

        $setBuilder = $this->createMock(QueryBuilder::class);
        $setBuilder
            ->expects(self::once())
            ->method('addOrderBy')
            ->with('`t`.`activity_last`', 'desc')
            ->willReturn($setBuilder);

        $setBuilder
            ->expects(self::exactly(2))
            ->method('setFirstResult');
        $setBuilder
            ->expects(self::exactly(2))
            ->method('setMaxResults');

        $setBuilder
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn(
                [
                    [
                        'id' => '4',
                        'attributes.name' => 'bar',
                        'attributes.foo.bar' => 'baz',
                    ],
                ],
            );

        $setBuilder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('99');

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('apply')
            ->with($setBuilder);

        $applier
            ->method('getTableAlias')
            ->willReturn('t');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($setBuilder);

        $hydrator = $this->createMock(Hydrator::class);
        $hydrator
            ->expects(self::once())
            ->method('hydrate')
            ->with(
                ResourceModel::class,
                [
                    'id' => '4',
                    'attributes' => [
                        'name' => 'bar',
                        'foo' => [
                            'bar' => 'baz',
                        ],
                    ],
                ],
            )
            ->willReturn($model);

        $mock = new class ($connection, $hydrator, $applier) extends AbstractDbalResourceRepository {
            public function __construct(Connection $connection, Hydrator $hydrator, private readonly ResourceApplier $applier)
            {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                return ResourceModel::class;
            }

            protected function getNoResourceWithIdClass(): string
            {
                throw new RuntimeException();
            }
        };

        $paginate = new Paginate(new PerPage(4), new Page(3));
        $set = $mock->getByLastActivity($paginate);

        self::assertSame(99, $set->count());

        foreach ($set as $item) {
            self::assertSame($model, $item);
        }
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
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', '`t`');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
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

        $mock = new class ($connection, $hydrator, $applier) extends AbstractDbalResourceRepository {
            public function __construct(Connection $connection, Hydrator $hydrator, private readonly ResourceApplier $applier)
            {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                throw new RuntimeException();
            }

            protected function getNoResourceWithIdClass(): string
            {
                throw new RuntimeException();
            }
        };

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
            ->method('getTableAlias')
            ->willReturn('t');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('from')
            ->with('`table`', '`t`');

        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
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

        $mock = new class ($connection, $hydrator, $applier, $e::class) extends AbstractDbalResourceRepository {
            public function __construct(
                Connection $connection,
                Hydrator $hydrator,
                private readonly ResourceApplier $applier,
                private readonly string $noResourceWithId,
            ) {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                throw new RuntimeException();
            }

            protected function getNoResourceWithIdClass(): string
            {
                return $this->noResourceWithId;
            }
        };

        $mock->getCurrentVersion($id);
    }

    public function testInvalidLastUnflatten(): void
    {
        $this->expectException(RuntimeException::class);

        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchAssociative')
            ->willReturn(
                [
                    'id' => '4',
                    'attributes.name.bar' => 'baz',
                    'attributes.name' => 'bar',
                ],
            );

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('apply')
            ->with($builder);

        $applier
            ->method('getTableAlias')
            ->willReturn('t');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);
        $hydrator
            ->expects(self::never())
            ->method('hydrate');

        $mock = new class ($connection, $hydrator, $applier) extends AbstractDbalResourceRepository {
            public function __construct(Connection $connection, Hydrator $hydrator, private readonly ResourceApplier $applier)
            {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                return ResourceModel::class;
            }

            protected function getNoResourceWithIdClass(): string
            {
                throw new RuntimeException();
            }
        };

        $mock->getWithId($id);
    }

    public function testUnflattedNonArray(): void
    {
        $this->expectException(RuntimeException::class);

        $id = new Identifier('517bf6e8-f812-426d-b503-d3de5619cac5');

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::once())
            ->method('andWhere')
            ->with('`t`.id = :id')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $id)
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('fetchAssociative')
            ->willReturn(
                [
                    'id' => '4',
                    'attributes.name' => 'bar',
                    'attributes.name.bar' => 'baz',
                ],
            );

        $applier = $this->createMock(ResourceApplier::class);
        $applier
            ->expects(self::once())
            ->method('apply')
            ->with($builder);

        $applier
            ->method('getTableAlias')
            ->willReturn('t');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $hydrator = $this->createMock(Hydrator::class);
        $hydrator
            ->expects(self::never())
            ->method('hydrate');

        $mock = new class ($connection, $hydrator, $applier) extends AbstractDbalResourceRepository {
            public function __construct(
                Connection $connection,
                Hydrator $hydrator,
                private readonly ResourceApplier $applier,
            ) {
                parent::__construct($connection, $hydrator);
            }

            protected function getResourceApplier(): ResourceApplier
            {
                return $this->applier;
            }

            protected function getResourceModelClass(): string
            {
                return ResourceModel::class;
            }

            protected function getNoResourceWithIdClass(): string
            {
            }
        };

        $mock->getWithId($id);
    }
}
