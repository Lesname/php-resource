<?php
declare(strict_types=1);

namespace LessResource\Service;

use LessResource\Service\Exception\NoResource;
use LessValueObject\String\Format\Resource\Identifier;

interface ResourceService
{
    public function exists(Identifier $id): bool;

    /**
     * @throws NoResource
     */
    public function getCurrentVersion(Identifier $id): int;
}
