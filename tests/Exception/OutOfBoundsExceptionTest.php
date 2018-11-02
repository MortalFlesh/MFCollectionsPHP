<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use MF\Collection\AbstractTestCase;

class OutOfBoundsExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            \OutOfBoundsException::class,
            CollectionExceptionInterface::class,
            OutOfBoundsException::class,
        ];

        $exception = new OutOfBoundsException();

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }
}
