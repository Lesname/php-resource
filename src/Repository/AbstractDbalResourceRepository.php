<?php
declare(strict_types=1);

namespace LessResource\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use JsonException;
use LessDatabase\Query\Builder\Applier\PaginateApplier;
use LessHydrator\Hydrator;
use LessResource\Model\ResourceModel;
use LessResource\Repository\Dbal\Applier\ResourceApplier;
use LessResource\Repository\Exception\AbstractNoResourceWithId;
use LessResource\Repository\Exception\NoResourceFromBuilder;
use LessResource\Set\ArrayResourceSet;
use LessResource\Set\ResourceSet;
use LessValueObject\Composite\Paginate;
use LessValueObject\Enum\OrderDirection;
use LessValueObject\String\Format\Resource\Identifier;
use RuntimeException;

/**
 * @implements ResourceRepository<T>
 *
 * @template T of \LessResource\Model\ResourceModel
 */
abstract class AbstractDbalResourceRepository implements ResourceRepository
{
    abstract protected function getResourceApplier(): ResourceApplier;

    /**
     * @return class-string<T>
     */
    abstract protected function getResourceModelClass(): string;

    /**
     * @return class-string<AbstractNoResourceWithId>
     */
    abstract protected function getNoResourceWithIdClass(): string;

    public function __construct(
        protected Connection $connection,
        protected Hydrator $hydrator
    ) {}

    /**
     * @throws Exception
     */
    public function exists(Identifier $id): bool
    {
        $builder = $this->createBaseBuilder();
        $builder->select('count(*)');
        $this->applyWhereId($builder, $id);

        return $builder->fetchOne() > 0;
    }

    /**
     * @throws AbstractNoResourceWithId
     * @throws Exception
     * @throws JsonException
     */
    public function getWithId(Identifier $id): ResourceModel
    {
        $builder = $this->createResourceBuilder();
        $this->applyWhereId($builder, $id);

        try {
            return $this->getResourceFromBuilder($builder);
        } catch (NoResourceFromBuilder) {
            $class = $this->getNoResourceWithIdClass();
            throw new $class($id);
        }
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function getByLastActivity(Paginate $paginate): ResourceSet
    {
        return $this->getResourceSetFromBuilder(
            $this->getByLastActivityBuilder($paginate),
        );
    }

    protected function getByLastActivityBuilder(Paginate $paginate, ?OrderDirection $direction = null): QueryBuilder
    {
        $builder = $this->createResourceBuilder();
        (new PaginateApplier($paginate))->apply($builder);

        $direction ??= OrderDirection::Descending;
        $applier = $this->getResourceApplier();
        $builder->addOrderBy("`{$applier->getTableAlias()}`.`activity_last`", $direction->asSQL());

        return $builder;
    }

    /**
     * @throws AbstractNoResourceWithId
     * @throws Exception
     */
    public function getCurrentVersion(Identifier $id): int
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select('version');
        $this->applyWhereId($builder, $id);

        $applier = $this->getResourceApplier();
        $builder->from("`{$applier->getTableName()}`", "`{$applier->getTableAlias()}`");

        $result = $builder->fetchOne();
        assert(is_string($result) || $result === false);

        if ($result === false) {
            $class = $this->getNoResourceWithIdClass();
            throw new $class($id);
        }

        return (int)$result;
    }

    /**
     * @return T
     *
     * @throws JsonException
     * @throws NoResourceFromBuilder
     * @throws Exception
     */
    protected function getResourceFromBuilder(QueryBuilder $builder): ResourceModel
    {
        $associative = $builder->fetchAssociative();

        if ($associative === false) {
            throw new NoResourceFromBuilder();
        }

        return $this->hydrateResource($associative);
    }

    /**
     * @return array<int, T>
     *
     * @throws JsonException
     * @throws Exception
     */
    protected function getResourcesFromBuilder(QueryBuilder $builder): array
    {
        return array_map(
            fn (array $associative) => $this->hydrateResource($associative),
            $builder->fetchAllAssociative(),
        );
    }

    /**
     * @return ResourceSet<T>
     *
     * @throws JsonException
     * @throws Exception
     */
    protected function getResourceSetFromBuilder(QueryBuilder $builder): ResourceSet
    {
        return new ArrayResourceSet(
            $this->getResourcesFromBuilder($builder),
            $this->getCountFromResultsBuilder($builder),
        );
    }

    /**
     * @throws Exception
     */
    protected function getCountFromResultsBuilder(QueryBuilder $builder): int
    {
        $countBuilder = clone $builder;

        $countBuilder->select("count(distinct {$this->getIdColumn()})");

        $countBuilder->resetQueryPart('orderBy');
        $countBuilder->resetQueryPart('distinct');
        $countBuilder->resetQueryPart('groupBy');
        $countBuilder->resetQueryPart('having');

        // Resets limit/offset
        $countBuilder->setMaxResults(1);
        $countBuilder->setFirstResult(0);

        $result = $countBuilder->fetchOne();
        assert(is_string($result) || is_int($result));

        return (int)$result;
    }

    protected function createResourceBuilder(): QueryBuilder
    {
        $builder = $this->connection->createQueryBuilder();
        $this->getResourceApplier()->apply($builder);

        return $builder;
    }

    protected function createBaseBuilder(): QueryBuilder
    {
        $builder = $this->connection->createQueryBuilder();

        $applier = $this->getResourceApplier();
        $builder->from("`{$applier->getTableName()}`", "`{$applier->getTableAlias()}`");

        return $builder;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return T
     *
     * @throws JsonException
     */
    protected function hydrateResource(array $array): ResourceModel
    {
        return $this->hydrator->hydrate(
            $this->getResourceModelClass(),
            $this->decode($array),
        );
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     *
     * @throws JsonException
     *
     * @psalm-suppress MixedAssignment
     */
    protected function decode(array $array): array
    {
        foreach ($this->getJsonFields() as $field) {
            if (isset($array[$field]) && is_string($array[$field])) {
                $array[$field] = json_decode($array[$field],  true, flags: JSON_THROW_ON_ERROR);
            }
        }

        return $this->unflatten($array);
    }

    /**
     * @return iterable<string>
     */
    protected function getJsonFields(): iterable
    {
        return [];
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     *
     * @psalm-suppress MixedAssignment
     */
    protected function unflatten(array $array): array
    {
        $output = [];

        foreach ($array as $key => $value) {
            $keyParts = explode('.', $key);
            $last = array_key_last($keyParts);
            $paste = &$output;

            foreach ($keyParts as $i => $keyPart) {
                if ($i === $last) {
                    if (array_key_exists($keyPart, $paste)) {
                        throw new RuntimeException();
                    }

                    $paste[$keyPart] = $value;
                } else {
                    if (!array_key_exists($keyPart, $paste)) {
                        $paste[$keyPart] = [];
                    } elseif (!is_array($paste[$keyPart])) {
                        throw new RuntimeException();
                    }

                    $paste = &$paste[$keyPart];
                }
            }
        }

        return $output;
    }

    protected function applyWhereId(QueryBuilder $builder, Identifier $id): void
    {
        $builder->andWhere("{$this->getIdColumn()} = :id");
        $builder->setParameter('id', $id);
    }

    protected function getIdColumn(): string
    {
        $applier = $this->getResourceApplier();

        return "`{$applier->getTableAlias()}`.id";
    }
}
