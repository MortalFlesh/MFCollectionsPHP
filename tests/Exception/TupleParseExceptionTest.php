<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use Assert\AssertionFailedException;
use MF\Collection\AbstractTestCase;

class TupleParseExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            \InvalidArgumentException::class,
            AssertionFailedException::class,
            CollectionExceptionInterface::class,
            TupleExceptionInterface::class,
            TupleParseException::class,
        ];

        $exception = new TupleParseException('message');

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }

    public function testShouldNotBeCatchableByInstances(): void
    {
        $notExpectedExceptions = [
            \BadMethodCallException::class,
        ];

        $exception = new TupleParseException('message');

        foreach ($notExpectedExceptions as $notExpectedException) {
            $this->assertNotInstanceOf($notExpectedException, $exception);
        }
    }
}
