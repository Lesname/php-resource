<?php
declare(strict_types=1);

namespace LessResourceTest\Model;

use LessResource\Model\AbstractResourceModel;
use LessValueObject\Composite\ForeignReference;
use LessValueObject\String\Format\Resource\Id;
use LessValueObject\String\Format\Resource\Type;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LessResource\Model\AbstractResourceModel
 */
final class AbstractResourceModelTest extends TestCase
{
    public function testGetForeignReference(): void
    {
        $id = new Id('0eb72b3f-7ab6-4c14-87f2-83ab8813eb15');
        $type = new Type('fiz');

        $mock = $this->getMockForAbstractClass(
            AbstractResourceModel::class,
            [
                $id,
                $type,
            ],
        );

        self::assertEquals(
            new ForeignReference($type, $id),
            $mock->getForeignReference(),
        );
    }
}
