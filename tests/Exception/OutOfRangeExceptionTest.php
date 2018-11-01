<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use MF\Collection\AbstractTestCase;

class OutOfRangeExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            \OutOfRangeException::class,
            CollectionExceptionInterface::class,
            OutOfRangeException::class,
        ];

        $exception = new OutOfRangeException();

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }
}
