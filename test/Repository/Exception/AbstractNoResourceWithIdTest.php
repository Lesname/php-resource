<?php
declare(strict_types=1);

namespace LessResourceTest\Repository\Exception;

use LessResource\Repository\Exception\AbstractNoResourceWithId;
use LessValueObject\String\Format\Resource\Identifier;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LessResource\Repository\Exception\AbstractNoResourceWithId
 */
final class AbstractNoResourceWithIdTest extends TestCase
{
    public function testConstruct(): void
    {
        $id = new Identifier('70f02b6f-1e31-4c61-bbf9-791c8478faf8');

        $e = new class ($id) extends AbstractNoResourceWithId {
        };

        self::assertSame($id, $e->id);
    }
}
