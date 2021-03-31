<?php declare(strict_types=1);

namespace MF\Collection;

use Assert\Assertion as BaseAssertion;
use MF\Collection\Exception\InvalidArgumentException;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = InvalidArgumentException::class;

    public static function isValidKey(mixed $key): void
    {
        static::false(is_object($key), 'Key cannot be an Object');
        static::false(is_array($key), 'Key cannot be an Array');
    }
}
