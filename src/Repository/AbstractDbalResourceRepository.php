<?php
declare(strict_types=1);

namespace LesResource\Repository;

use Override;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use JsonException;
use LesValueObject\Collection\Identifiers;
use LesDatabase\Query\Builder\Helper\LabelHelper;
use LesDatabase\Query\Builder\Applier\PaginateApplier;
use LesHydrator\Hydrator;
use LesResource\Model\ResourceModel;
use LesResource\Repository\Dbal\Applier\ResourceApplier;
use LesResource\Repository\Exception\AbstractNoResourceWithId;
use LesResource\Repository\Exception\NoResourceFromBuilder;
use LesResource\Set\ArrayResourceSet;
use LesResource\Set\ResourceSet;
use LesValueObject\Composite\Paginate;
use LesValueObject\Enum\OrderDirection;
use LesValueObject\String\Format\Resource\Identifier;
use RuntimeException;

/**
 * @implements ResourceRepository<T>
 *
 * @template T of \LesResource\Model\ResourceModel
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
    #[Override]
    public function exists(Identifier $id): bool
    {
        $builder = $this->createBaseBuilder();
        $builder->select('count(*)');
        $this->applyWhereId($builder, $id);

        return $builder->fetchOne() > 0;
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    #[Override]
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
    #[Override]
    public function getWithIds(Identifiers $ids): ResourceSet
    {
        $builder = $this->createResourceBuilder();
        $whereInList = [];
        $orderByList = [];

        $position = 0;

        foreach ($ids as $id) {
            $label = LabelHelper::fromValue($id);
            $builder->setParameter($label, $id->value);

            $orderByList[] = "when :{$label} then {$position}";
            $whereInList[] = ":{$label}";

            $position += 1;
        }

        $whereInSqlList = implode(', ', $whereInList);
        $builder->andWhere("{$this->getIdColumn()} IN ({$whereInSqlList})");

        $orderBySqlList = implode(' ', $orderByList);
        $builder->addOrderBy("case {$this->getIdColumn()} {$orderBySqlList} end", 'ASC');

        return $this->getResourceSetFromBuilder($builder);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    #[Override]
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
     * @throws Exception
     */
    #[Override]
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
            fn(array $associative) => $this->hydrateResource($associative),
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
     * @return int<0, max>
     *
     * @throws Exception
     */
    protected function getCountFromResultsBuilder(QueryBuilder $builder): int
    {
        $countBuilder = clone $builder;

        $countBuilder->select("count(distinct {$this->getIdColumn()})");
        $countBuilder->resetOrderBy();
        $countBuilder->resetGroupBy();
        $countBuilder->resetHaving();
        $countBuilder->distinct(false);

        // Resets limit/offset
        $countBuilder->setMaxResults(1);
        $countBuilder->setFirstResult(0);

        $result = $countBuilder->fetchOne();

        if (is_string($result) && ctype_digit($result)) {
            $result = (int) $result;
        }

        if (!is_int($result)) {
            throw new RuntimeException();
        }

        if ($result < 0) {
            throw new RuntimeException();
        }

        return $result;
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
                $array[$field] = json_decode($array[$field], true, flags: JSON_THROW_ON_ERROR);
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
     * @psalm-suppress MixedReturnTypeCoercion
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
