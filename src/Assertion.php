<?php declare(strict_types=1);

namespace MF\Collection;

use Assert\Assertion as BaseAssertion;
use MF\Collection\Exception\InvalidArgumentException;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = InvalidArgumentException::class;

    /**
     * @param mixed $callback
     * @param string|callable|null $message
     * @param string|null $propertyPath
     */
    public static function isCallable($callback, $message = null, $propertyPath = null): void
    {
        parent::isCallable($callback, $message ?? 'Callback must be callable but %s given.', $propertyPath);
    }

    /** @param mixed $key */
    public static function isValidKey($key): void
    {
        static::false(is_object($key), 'Key cannot be an Object');
        static::false(is_array($key), 'Key cannot be an Array');
    }
}
