<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use Assert\AssertionFailedException;
use MF\Collection\AbstractTestCase;

class InvalidArgumentExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            \InvalidArgumentException::class,
            AssertionFailedException::class,
            CollectionExceptionInterface::class,
            InvalidArgumentException::class,
        ];

        $exception = new InvalidArgumentException('message');

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }
}
