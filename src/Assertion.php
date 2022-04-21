<?php declare(strict_types=1);

namespace MF\Collection;

use Assert\Assertion as BaseAssertion;
use MF\Collection\Exception\InvalidArgumentException;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = InvalidArgumentException::class;

    public static function isKey(mixed $offset): void
    {
        static::true(
            is_string($offset) || is_int($offset),
            'Offset "%s" is not a key. Key must be a int|string.',
        );
    }
}
