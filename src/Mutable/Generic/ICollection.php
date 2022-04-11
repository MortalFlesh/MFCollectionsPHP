<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Generic\IEnumerable;

/**
 * @phpstan-template TKey of int|string
 * @phpstan-template TValue
 *
 * @phpstan-extends IEnumerable<TKey, TValue>
 */
interface ICollection extends IEnumerable
{
    /** @phpstan-param TValue $value */
    public function contains(mixed $value): bool;

    /** @phpstan-param callable(TValue, TKey): bool $callback */
    public function containsBy(callable $callback): bool;

    /** @phpstan-return array<TKey, TValue> */
    public function toArray(): array;

    /** @param callable(TValue, TKey): void $callback */
    public function each(callable $callback): void;

    /**
     * Tests if all elements of the collection satisfy the given predicate.
     *
     * @phpstan-param callable(TValue, TKey): bool $predicate
     */
    public function forAll(callable $predicate): bool;

    public function implode(string $glue): string;
}
