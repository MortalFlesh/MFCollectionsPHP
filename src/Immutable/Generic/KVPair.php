<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Immutable\ITuple;
use MF\Collection\Immutable\Tuple;

/**
 * @phpstan-template TKey of int|string
 * @phpstan-template TValue
 */
readonly class KVPair
{
    /**
     * @phpstan-param self<TKey, TValue> $pair
     * @phpstan-return TKey
     */
    public static function key(self $pair): int|string
    {
        return $pair->getKey();
    }

    /**
     * @phpstan-param self<TKey, TValue> $pair
     * @phpstan-return TValue
     */
    public static function value(self $pair): mixed
    {
        return $pair->getValue();
    }

    public static function fromTuple(ITuple $tuple): static
    {
        return new static($tuple->first(), $tuple->second());
    }

    /**
     * @phpstan-param TKey $key
     * @phpstan-param TValue $value
     */
    public function __construct(private int|string $key, private mixed $value)
    {
    }

    /** @phpstan-return TKey */
    public function getKey(): int|string
    {
        return $this->key;
    }

    /** @phpstan-return TValue */
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function asTuple(): ITUple
    {
        return Tuple::of($this->key, $this->value);
    }
}
