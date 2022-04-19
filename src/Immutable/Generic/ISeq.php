<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Exception\OutOfBoundsException;
use MF\Collection\Range;

/**
 * @phpstan-type TIndex int
 * @phpstan-template TValue
 * @phpstan-type DataSource iterable<TValue>|callable(): iterable<TValue>
 *
 * @phpstan-import-type RangeDefinition from Range
 *
 * @phpstan-extends ICollection<TIndex, TValue>
 */
interface ISeq extends ICollection
{
    public const INFINITE = Range::INFINITE;

    /**
     * @phpstan-template T
     * @phpstan-param ISeq<T|iterable<T>> $seq
     * @phpstan-return ISeq<T>
     */
    public static function concatSeq(ISeq $seq): ISeq;

    /**
     * Seq::of(1, 2, 3)
     * Seq::of(...$array, ...$array2)
     *
     * @phpstan-param TValue $args
     * @phpstan-return ISeq<TValue>
     */
    public static function of(mixed ...$args): ISeq;

    /**
     * @phpstan-param iterable<mixed, TValue> $source
     * @phpstan-return ISeq<TValue>
     */
    public static function from(iterable $source): ISeq;

    /**
     * Seq::create([1,2,3], ($i) => $i * 2)
     * Seq::create(range(1, 10), ($i) => $i * 2)
     * Seq::create($list, ($i) => $i * 2)
     *
     * @phpstan-template T
     *
     * @phpstan-param iterable<mixed, T> $iterable
     * @phpstan-param callable(T): TValue $creator
     * @phpstan-return ISeq<TValue>
     */
    public static function create(iterable $iterable, callable $creator): ISeq;

    /**
     * Alias for empty sequence
     * Seq::create([])
     *
     * @phpstan-return ISeq<TValue>
     */
    public static function createEmpty(): ISeq;

    /**
     * Seq::range([1, 10])
     * Seq::range([1, 10, 100])
     * Seq::range('1..10')
     * Seq::range('1..10..100')
     *
     * @phpstan-param RangeDefinition $range
     * @phpstan-return ISeq<int>
     */
    public static function range(string|array $range): ISeq;

    /**
     * Alias for infinite range 1..Inf
     * Seq::range('1..Inf')
     *
     * @phpstan-return ISeq<int>
     */
    public static function infinite(): ISeq;

    /**
     * F#: seq { for i in 1 .. 10 do yield i * i }
     * Seq::forDo(fn($i) => yield $i * $i, '1 .. 10')
     * 1, 4, 9, ... 100
     *
     * F#: seq { for i in 1 .. 10 -> i * i }
     * Seq::forDo(fn($i) => $i * $i, '1 .. 10')
     * 1, 4, 9, ... 100
     *
     * F#: seq { for i in 1 .. 10 do if $i % 2 === 0 then yield i }
     * Seq::forDo(fn($i) => if($i % 2 === 0) yield $i, '1 .. 10')
     * 2, 4, 6, 8, 10
     *
     * F#:
     * let (height, width) = (10, 10)
     * seq { for row in 0 .. width - 1 do
     *      for col in 0 .. height - 1 do
     *          yield (row, col, row*width + col)
     * }
     * If you need more complex for loops for generating, use ISeq::init() instead
     *
     * @phpstan-param RangeDefinition $range
     * @phpstan-param callable(int): TValue $callable
     * @phpstan-return ISeq<TValue>
     */
    public static function forDo(string|array $range, callable $callable): ISeq;

    /**
     * Seq::init(function() {
     *     while($line = readLines($file)){
     *         yield $line;
     *     }
     * })
     *
     * Seq::init(function() {
     *     while($database->hasBatch()){
     *         yield $database->fetchBatch();
     *     }
     * })
     *
     * @phpstan-param DataSource $iterable
     * @phpstan-return ISeq<TValue>
     */
    public static function init(iterable|callable $iterable): ISeq;

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State): array{0: State, 1: State|null} $callable
     * @phpstan-param State $initialValue
     * @phpstan-return ISeq<State>
     */
    public static function unfold(callable $callable, mixed $initialValue): ISeq;

    /** @phpstan-return TValue[] */
    public function toArray(): array;

    /**
     * Seq takes exactly n items from sequence.
     * Note: If there is not enough items, it will throw an exception.
     *
     * @phpstan-return ISeq<TValue>
     * @throws \OutOfRangeException
     */
    public function take(int $limit): ISeq;

    /**
     * Seq takes items till callable will return false.
     * Note: Item and Key given to callable will be mapped by given mapping.
     *
     * @example
     * Seq::range('1..Inf')->takeWhile(fn($i) => $i < 100) creates [1, 2, 3, ..., 99]
     * Seq::infinite()->filter(fn($i) => $i % 2 === 0)->map(fn($i) => $i * $i)->takeWhile(fn($i) => $i < 25)->toArray(); creates [4, 16]
     *
     * @phpstan-param callable(TValue, TIndex=): bool $callable
     * @phpstan-return ISeq<TValue>
     */
    public function takeWhile(callable $callable): ISeq;

    /**
     * Seq takes up to n items from sequence.
     * Note: If there is not enough items, it will return all items.
     *
     * @phpstan-return ISeq<TValue>
     */
    public function takeUpTo(int $limit): ISeq;

    /**
     * Returns a sequence that skips N elements of the underlying sequence and then yields the remaining elements of the sequence.
     *
     * @phpstan-return ISeq<TValue>
     */
    public function skip(int $limit): ISeq;

    /**
     * Returns a sequence that, when iterated, skips elements of the underlying sequence while the given predicate returns True, and then yields the remaining elements of the sequence.
     *
     * @phpstan-param callable(TValue, TIndex=): bool $callable
     * @phpstan-return ISeq<TValue>
     */
    public function skipWhile(callable $callable): ISeq;

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State, TValue, TIndex=, ISeq<TValue>=): State $reducer
     * @phpstan-param State $initialValue
     * @phpstan-return State
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed;

    /**
     * @phpstan-param callable(TValue, TIndex=): bool $callback
     * @phpstan-return ISeq<TValue>
     */
    public function filter(callable $callback): ISeq;

    /** @phpstan-param TValue $value */
    public function contains(mixed $value): bool;

    /** @phpstan-param callable(TValue, TIndex=): bool $callback */
    public function containsBy(callable $callback): bool;

    public function isEmpty(): bool;

    /**
     * @phpstan-return ISeq<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function sort(): ISeq;

    /**
     * @phpstan-return ISeq<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function sortDescending(): ISeq;

    /**
     * @phpstan-param callable(TValue, TValue): int<-1, 1> $callback
     * @phpstan-return ISeq<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function sortBy(callable $callback): ISeq;

    /**
     * @phpstan-param callable(TValue): int<-1, 1> $callback
     * @phpstan-return ISeq<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function sortByDescending(callable $callback): ISeq;

    /**
     * Keeps only unique values inside the list.
     *
     * @phpstan-return ISeq<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function unique(): ISeq;

    /**
     * Keeps only unique values by a given callback inside the list.
     *
     * @phpstan-template Unique
     *
     * @phpstan-param callable(TValue): Unique $callback
     * @phpstan-return ISeq<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function uniqueBy(callable $callback): ISeq;

    /**
     * Sort all items in a reverse order.
     *
     * @phpstan-return ISeq<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function reverse(): ISeq;

    public function sum(): int|float;

    /** @phpstan-param callable(TValue): (int|float) $callback */
    public function sumBy(callable $callback): int|float;

    /** @phpstan-return ISeq<TValue> */
    public function clear(): ISeq;

    /**
     * @phpstan-param ISeq<TValue> $seq
     * @phpstan-return ISeq<TValue>
     */
    public function append(ISeq $seq): ISeq;

    /**
     * Divides the seq into chunks of size at most chunkSize.
     *
     * @phpstan-return ISeq<ISeq<TValue>>
     *
     * @throws InvalidArgumentException
     */
    public function chunkBySize(int $size): ISeq;

    /**
     * Splits the seq into at most count chunks.
     *
     * @phpstan-param int<1, max> $count
     * @phpstan-return ISeq<ISeq<TValue>>
     *
     * @throws OutOfBoundsException
     * @throws InvalidArgumentException
     */
    public function splitInto(int $count): ISeq;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return ISeq<T>
     */
    public function map(callable $callback): ISeq;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TIndex): T $callback
     * @phpstan-return ISeq<T>
     */
    public function mapi(callable $callback): ISeq;

    /** @phpstan-param callable(TValue, TIndex=): void $callback */
    public function each(callable $callback): void;

    /**
     * Applies the given function to each element of the sequence and concatenates all the results
     *
     * Note: if mapping is not necessary, you can use just concat instead
     * @see ISeq::concat()
     *
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): iterable<T> $callback
     * @phpstan-return ISeq<T>
     */
    public function collect(callable $callback): ISeq;

    /**
     * Combines the given iterable-of-iterables as a single concatenated iterable
     *
     * Note: map->concat could be replaced by collect
     * @see ISeq::collect()
     *
     * @example Seq::from([ [1,2,3], [4,5,6] ])->concat()->toArray() // [1,2,3,4,5,6]
     *
     * @phpstan-return ISeq<TValue>  // todo - fix type
     */
    public function concat(): ISeq;

    /**
     * @phpstan-template TKey of int|string
     *
     * @phpstan-param callable(TValue): TKey $callback
     * @phpstan-return ISeq<KVPair<TKey, int>>
     */
    public function countBy(callable $callback): ISeq;

    /**
     * @phpstan-template TGroup of int|string
     *
     * @phpstan-param callable(TValue): TGroup $callback
     * @phpstan-return ISeq<KVPair<TGroup, ISeq<TValue>>>
     */
    public function groupBy(callable $callback): ISeq;

    /**
     * @phpstan-return TValue|null
     *
     * @throws OutOfBoundsException
     */
    public function min(): mixed;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return TValue|null
     *
     * @throws OutOfBoundsException
     */
    public function minBy(callable $callback): mixed;

    /**
     * @phpstan-return TValue|null
     *
     * @throws OutOfBoundsException
     */
    public function max(): mixed;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return TValue|null
     *
     * @throws OutOfBoundsException
     */
    public function maxBy(callable $callback): mixed;

    public function implode(string $glue): string;

    /**
     * @phpstan-return IList<TValue>
     *
     * @throws OutOfBoundsException
     */
    public function toList(): IList;
}
