<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use MF\Collection\AbstractTestCase;

class TupleBadMethodCallExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            \BadMethodCallException::class,
            CollectionExceptionInterface::class,
            BadMethodCallException::class,
            TupleExceptionInterface::class,
            TupleBadMethodCallException::class,
        ];

        $exception = new TupleBadMethodCallException();

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }

    public function testShouldNotBeCatchableByInstances(): void
    {
        $notExpectedExceptions = [
            \InvalidArgumentException::class,
        ];

        $exception = new TupleBadMethodCallException();

        foreach ($notExpectedExceptions as $notExpectedException) {
            $this->assertNotInstanceOf($notExpectedException, $exception);
        }
    }
}
