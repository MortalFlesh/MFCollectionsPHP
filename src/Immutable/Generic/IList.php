<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Exception\InvalidArgumentException;

/**
 * @phpstan-type TIndex int
 * @phpstan-template TValue
 *
 * @phpstan-extends ICollection<TIndex, TValue>
 */
interface IList extends ICollection
{
    /**
     * @phpstan-template T
     * @phpstan-param IList<T|iterable<T>> $list
     * @phpstan-return IList<T>
     */
    public static function concatList(IList $list): IList;

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
     * @phpstan-param callable(TValue, TIndex=): bool $callback
     * @phpstan-return TValue|null
     */
    public function firstBy(callable $callback): mixed;

    /** @phpstan-return TValue|null */
    public function last(): mixed;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function add(mixed $value): IList;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function unshift(mixed $value): IList;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function removeFirst(mixed $value): IList;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function removeAll(mixed $value): IList;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return IList<T>
     */
    public function map(callable $callback): IList;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TIndex): T $callback
     * @phpstan-return IList<T>
     */
    public function mapi(callable $callback): IList;

    /**
     * @phpstan-param callable(TValue, TIndex=): bool $callback
     * @phpstan-return IList<TValue>
     */
    public function filter(callable $callback): IList;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TIndex=): (T|null) $callback
     * @phpstan-return IList<T>
     */
    public function choose(callable $callback): IList;

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State, TValue, TIndex=, IList<TValue>=): State $reducer
     * @phpstan-param State $initialValue
     * @phpstan-return State
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed;

    /** @phpstan-return IList<TValue> */
    public function sort(): IList;

    /** @phpstan-return IList<TValue> */
    public function sortDescending(): IList;

    /**
     * @phpstan-param callable(TValue, TValue): (string|int|float) $callback
     * @phpstan-return IList<TValue>
     */
    public function sortBy(callable $callback): IList;

    /**
     * @phpstan-param callable(TValue, TIndex=): (string|int|float) $callback
     * @phpstan-return IList<TValue>
     */
    public function sortByDescending(callable $callback): IList;

    /**
     * Keeps only unique values inside the list.
     *
     * @phpstan-return IList<TValue>
     */
    public function unique(): IList;

    /**
     * Keeps only unique values by a given callback inside the list.
     *
     * @phpstan-template Unique
     *
     * @phpstan-param callable(TValue, TIndex=): Unique $callback
     * @phpstan-return IList<TValue>
     */
    public function uniqueBy(callable $callback): IList;

    /**
     * Sort all items in a reverse order.
     *
     * @phpstan-return IList<TValue>
     */
    public function reverse(): IList;

    public function sum(): int|float;

    /** @phpstan-param callable(TValue, TIndex=): (int|float) $callback */
    public function sumBy(callable $callback): int|float;

    /** @phpstan-return IList<TValue> */
    public function clear(): IList;

    /**
     * @phpstan-param IList<TValue> $list
     * @phpstan-return IList<TValue>
     */
    public function append(IList $list): IList;

    /**
     * Divides the list into chunks of size at most chunkSize.
     *
     * @phpstan-param int<1, max> $size
     * @phpstan-return IList<IList<TValue>>
     *
     * @throws InvalidArgumentException
     */
    public function chunkBySize(int $size): IList;

    /**
     * Splits the list into at most count chunks.
     *
     * @phpstan-param int<1, max> $count
     * @phpstan-return IList<IList<TValue>>
     *
     * @throws InvalidArgumentException
     */
    public function splitInto(int $count): IList;

    /**
     * For each element of the list, applies the given function.
     * Concatenates all the results and return the combined list.
     *
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): iterable<T> $callback
     * @phpstan-return IList<T>
     */
    public function collect(callable $callback): IList;

    /**
     * Returns a new list that contains the elements of each the lists in order.
     *
     * @phpstan-return IList<TValue>
     */
    public function concat(): IList;

    /**
     * @phpstan-template TKey of int|string
     *
     * @phpstan-param callable(TValue, TIndex=): TKey $callback
     * @phpstan-return IList<KVPair<TKey, int>>
     */
    public function countBy(callable $callback): IList;

    /**
     * @phpstan-template TGroup of int|string
     *
     * @phpstan-param callable(TValue): TGroup $callback
     * @phpstan-return IList<KVPair<TGroup, IList<TValue>>>
     */
    public function groupBy(callable $callback): IList;

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

    /**
     * Splits the collection into two collections, containing the elements for which the given predicate returns True and False respectively.
     * Element order is preserved in both of the created lists.
     *
     * @phpstan-param callable(TValue, TIndex=): bool $predicate
     * @phpstan-return array{0:IList<TValue>, 1: IList<TValue>}
     */
    public function partition(callable $predicate): array;

    /** @phpstan-return \MF\Collection\Mutable\Generic\IList<TValue> */
    public function asMutable(): \MF\Collection\Mutable\Generic\IList;

    /** @phpstan-return ISeq<TValue> */
    public function toSeq(): ISeq;
}
