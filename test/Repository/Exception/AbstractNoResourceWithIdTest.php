<?php
declare(strict_types=1);

namespace LesResourceTest\Repository\Exception;

use LesResource\Repository\Exception\AbstractNoResourceWithId;
use LesValueObject\String\Format\Resource\Identifier;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesResource\Repository\Exception\AbstractNoResourceWithId
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
