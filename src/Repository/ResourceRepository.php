<?php

declare(strict_types=1);

namespace LesResource\Repository;

use LesResource\Model\ResourceModel;
use LesResource\Repository\Exception\NoResource;
use LesResource\Set\ResourceSet;
use LesValueObject\Composite\Paginate;
use LesResource\Repository\Parameters\Identifiers;
use LesValueObject\String\Format\Resource\Identifier;

/**
 * @template T of ResourceModel
 *
 * @psalm-mutable
 */
interface ResourceRepository
{
    /**
     * @psalm-impure
     */
    public function exists(Identifier $id): bool;

    /**
     * @return T
     *
     * @throws NoResource
     *
     * @psalm-impure
     */
    public function getWithId(Identifier $id): ResourceModel;

    /**
     * @return ResourceSet<T>
     *
     * @psalm-impure
     */
    public function getWithIds(Identifiers $ids): ResourceSet;

    /**
     * @return ResourceSet<T>
     *
     * @psalm-impure
     */
    public function getByLastActivity(Paginate $paginate): ResourceSet;

    /**
     * @throws NoResource
     *
     * @psalm-impure
     */
    public function getCurrentVersion(Identifier $id): int;
}
