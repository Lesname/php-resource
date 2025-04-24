<?php
declare(strict_types=1);

namespace LesResource\Repository;

use LesResource\Model\ResourceModel;
use LesValueObject\Collection\Identifiers;
use LesResource\Repository\Exception\NoResource;
use LesResource\Set\ResourceSet;
use LesValueObject\Composite\Paginate;
use LesValueObject\String\Format\Resource\Identifier;

/**
 * @template T of ResourceModel
 */
interface ResourceRepository
{
    public function exists(Identifier $id): bool;

    /**
     * @return T
     *
     * @throws NoResource
     */
    public function getWithId(Identifier $id): ResourceModel;

    /**
     * @return ResourceSet<T>
     */
    public function getWithIds(Identifiers $ids): ResourceSet;

    /**
     * @return ResourceSet<T>
     */
    public function getByLastActivity(Paginate $paginate): ResourceSet;

    /**
     * @throws NoResource
     */
    public function getCurrentVersion(Identifier $id): int;
}
