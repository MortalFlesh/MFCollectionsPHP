<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use MF\Collection\AbstractTestCase;

class BadMethodCallExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            \BadMethodCallException::class,
            CollectionExceptionInterface::class,
            BadMethodCallException::class,
        ];

        $exception = new BadMethodCallException();

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }
}
