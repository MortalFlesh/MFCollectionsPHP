<?php declare(strict_types=1);

namespace MF\Collection\Helper;

use MF\Collection\Mutable\Generic\ICollection;

/** @internal */
class Collection
{
    /**
     * @phpstan-template TKey of int|string
     * @phpstan-template TValue
     *
     * @phpstan-param ICollection<TKey, TValue> $collection
     * @phpstan-return array<TKey, TValue>
     */
    public static function mutableToArray(ICollection $collection): array
    {
        $array = [];

        foreach ($collection as $key => $value) {
            /** @var TValue $normalizedValue */
            $normalizedValue = $value instanceof ICollection || $value instanceof \MF\Collection\Immutable\Generic\ICollection
                ? $value->toArray()
                : $value;

            $array[$key] = $normalizedValue;
        }

        return $array;
    }

    /**
     * @phpstan-template TKey of int|string
     * @phpstan-template TValue
     *
     * @phpstan-param \MF\Collection\Immutable\Generic\ICollection<TKey, TValue> $collection
     * @phpstan-return array<TKey, TValue>
     */
    public static function immutableToArray(\MF\Collection\Immutable\Generic\ICollection $collection): array
    {
        $array = [];

        foreach ($collection as $key => $value) {
            /** @var TValue $normalizedValue */
            $normalizedValue = $value instanceof ICollection || $value instanceof \MF\Collection\Immutable\Generic\ICollection
                ? $value->toArray()
                : $value;

            $array[$key] = $normalizedValue;
        }

        return $array;
    }
}
