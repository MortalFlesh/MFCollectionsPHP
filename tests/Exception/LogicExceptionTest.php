<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use MF\Collection\AbstractTestCase;

class LogicExceptionTest extends AbstractTestCase
{
    public function testShouldBeCatchableByInstances(): void
    {
        $expectedExceptions = [
            \Throwable::class,
            \Exception::class,
            \LogicException::class,
            CollectionExceptionInterface::class,
            LogicException::class,
        ];

        $exception = new LogicException();

        foreach ($expectedExceptions as $expectedException) {
            $this->assertInstanceOf($expectedException, $exception);
        }
    }
}
