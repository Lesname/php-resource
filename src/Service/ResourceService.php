<?php
declare(strict_types=1);

namespace LessResource\Service;

use LessResource\Model\ResourceModel;
use LessResource\Service\Exception\NoResource;
use LessResource\Set\ResourceSet;
use LessValueObject\Composite\Paginate;
use LessValueObject\String\Format\Resource\Identifier;

/**
 * @template T of ResourceModel
 */
interface ResourceService
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
    public function getByLastActivity(Paginate $paginate): ResourceSet;

    /**
     * @throws NoResource
     */
    public function getCurrentVersion(Identifier $id): int;
}
