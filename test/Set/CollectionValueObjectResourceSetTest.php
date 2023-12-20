<?php
declare(strict_types=1);

namespace LessResourceTest\Set;

use LessResource\Model\ResourceModel;
use LessResource\Set\CollectionValueObjectResourceSet;
use PHPUnit\Framework\TestCase;
use LessValueObject\Collection\AbstractCollectionValueObject;

/**
 * @covers \LessResource\Set\CollectionValueObjectResourceSet
 */
class CollectionValueObjectResourceSetTest extends TestCase
{
    public function testIterate(): void
    {
        $resourceOne = $this->createMock(ResourceModel::class);
        $resourceTwo = $this->createMock(ResourceModel::class);

        $collection = new class ([$resourceOne, $resourceTwo]) extends AbstractCollectionValueObject {
            public static function getMinimumSize(): int
            {
                return 0;
            }

            public static function getMaximumSize(): int
            {
                return 10;
            }

            public static function getItemType(): string
            {
                return ResourceModel::class;
            }
        };

        $set = new CollectionValueObjectResourceSet($collection, 2);

        $array = iterator_to_array($set, false);
        self::assertCount(2, $array);

        self::assertSame($resourceOne, $array[0]);
        self::assertSame($resourceTwo, $array[1]);
    }
}
