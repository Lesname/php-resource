<?php
declare(strict_types=1);

namespace LesResourceTest\Set;

use LesResource\Model\ResourceModel;
use LesResource\Set\ArrayResourceSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesResource\Set\ArrayResourceSet
 */
final class ArrayResourceSetTest extends TestCase
{
    public function testSetup(): void
    {
        $model = $this->createMock(ResourceModel::class);

        $set = new ArrayResourceSet(
            [$model],
            2,
        );

        foreach ($set as $item) {
            self::assertSame($model, $item);
        }

        self::assertSame(2, $set->count());
        self::assertSame(
            [$model],
            $set->jsonSerialize(),
        );
    }
}
