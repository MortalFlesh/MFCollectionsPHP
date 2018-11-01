<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use MF\Collection\AbstractTestCase;

class TupleExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            CollectionExceptionInterface::class,
            TupleExceptionInterface::class,
            TupleException::class,
        ];

        $exception = new TupleException('message');

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }
}
