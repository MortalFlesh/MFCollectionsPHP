<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Immutable\Generic\ISeq;
use MF\Collection\Immutable\Generic\KVPair;

/**
 * @phpstan-type TIndex int
 * @phpstan-template TValue
 *
 * @phpstan-extends ICollection<TIndex, TValue>
 */
interface IList extends ICollection
{
    /**
     * @phpstan-param TValue $values
     * @phpstan-return IList<TValue>
     */
    public static function of(mixed ...$values): IList;

    /**
     * @phpstan-param iterable<mixed, TValue> $source
     * @phpstan-return IList<TValue>
     */
    public static function from(iterable $source): IList;

    /**
     * @phpstan-template T
     *
     * @phpstan-param iterable<int|string, T> $source
     * @phpstan-param callable(T, int|string): TValue $creator
     * @phpstan-return IList<TValue>
     */
    public static function create(iterable $source, callable $creator): IList;

    /** @phpstan-return TValue|null */
    public function first(): mixed;

    /**
     * @phpstan-param callable(TValue, TIndex): bool $callback
     * @phpstan-return TValue|null
     */
    public function firstBy(callable $callback): mixed;

    /** @phpstan-return TValue|null */
    public function last(): mixed;

    /** @phpstan-return TValue|null */
    public function shift(): mixed;

    /** @phpstan-return TValue|null */
    public function pop(): mixed;

    /** @phpstan-param TValue $value */
    public function add(mixed $value): void;

    /** @phpstan-param TValue $value */
    public function unshift(mixed $value): void;

    /** @phpstan-param TValue $value */
    public function removeFirst(mixed $value): void;

    /** @phpstan-param TValue $value */
    public function removeAll(mixed $value): void;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     */
    public function map(callable $callback): void;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TIndex): T $callback
     */
    public function mapi(callable $callback): void;

    /** @phpstan-param callable(TValue, TIndex=): bool $callback */
    public function filter(callable $callback): void;

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State, TValue, TIndex=, IList<TValue>=): State $reducer
     * @phpstan-param State $initialValue
     * @phpstan-return State
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed;

    public function sort(): void;

    public function sortDescending(): void;

    /** @phpstan-param callable(TValue, TValue): int<-1, 1> $callback */
    public function sortBy(callable $callback): void;

    /** @phpstan-param callable(TValue, TIndex=): int<-1, 1> $callback */
    public function sortByDescending(callable $callback): void;

    /** Keeps only unique values inside the list. */
    public function unique(): void;

    /**
     * Keeps only unique values by a given callback inside the list.
     *
     * @phpstan-template Unique
     *
     * @phpstan-param callable(TValue, TIndex=): Unique $callback
     */
    public function uniqueBy(callable $callback): void;

    /** Sort all items in a reverse order. */
    public function reverse(): void;

    public function sum(): int|float;

    /** @phpstan-param callable(TValue, TIndex=): (int|float) $callback */
    public function sumBy(callable $callback): int|float;

    public function clear(): void;

    /** @phpstan-param IList<TValue> $list */
    public function append(IList $list): void;

    /**
     * @phpstan-template TKey of int|string
     *
     * @phpstan-param callable(TValue, TIndex=): TKey $callback
     * @phpstan-return IList<KVPair<TKey, int>>
     */
    public function countBy(callable $callback): IList;

    /** @phpstan-return TValue|null */
    public function min(): mixed;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return TValue|null
     */
    public function minBy(callable $callback): mixed;

    /** @phpstan-return TValue|null */
    public function max(): mixed;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return TValue|null
     */
    public function maxBy(callable $callback): mixed;

    /** @phpstan-return \MF\Collection\Immutable\Generic\IList<TValue> */
    public function asImmutable(): \MF\Collection\Immutable\Generic\IList;

    /** @phpstan-return ISeq<TValue> */
    public function toSeq(): ISeq;
}
