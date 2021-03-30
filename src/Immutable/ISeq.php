<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

interface ISeq extends ICollection
{
    /**
     * Seq::of(1, 2, 3)
     * Seq::of(...$array, ...$array2)
     *
     * @param mixed $args
     * @return ISeq
     */
    public static function of(...$args): self;

    /** @return ISeq */
    public static function from(array $array, bool $recursive = false): self;

    /**
     * Seq::create([1,2,3], ($i) => $i * 2)
     * Seq::create(range(1, 10), ($i) => $i * 2)
     * Seq::create($list, ($i) => $i * 2)
     *
     * @return ISeq
     */
    public static function create(iterable $iterable, ?callable $callable = null): self;

    /**
     * Alias for empty sequence
     * Seq::create([])
     *
     * @return ISeq
     */
    public static function createEmpty(): self;

    /**
     * Seq::range([1, 10])
     * Seq::range([1, 10, 100])
     * Seq::range('1..10')
     * Seq::range('1..10..100')
     *
     * @param string|array $range
     * @return ISeq
     */
    public static function range($range): self;

    /**
     * Alias for infinite range 1..Inf
     * Seq::range('1..Inf')
     *
     * @return ISeq
     */
    public static function infinite(): self;

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
     * @param string|array $range string is for range '1..10'
     * @param callable $callable (int) => mixed
     * @return ISeq
     */
    public static function forDo($range, callable $callable): self;

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
     * @param iterable|callable $iterable string is for arrow function; Callable must be () => iterable
     * @return ISeq
     */
    public static function init($iterable): self;

    /**
     * @param callable $callable (State) => [State, State|null]
     * @param mixed $initialValue T: <State>
     * @return ISeq T: <State>
     */
    public static function unfold(callable $callable, $initialValue): self;

    public function toArray(): array;

    /**
     * Seq takes exactly n items from sequence.
     * Note: If there is not enough items, it will throw an exception.
     *
     * @throws \OutOfRangeException
     * @return ISeq
     */
    public function take(int $limit): self;

    /**
     * Seq takes items till callable will return false.
     * Note: Item and Key given to callable will be mapped by given mapping.
     *
     * @example
     * Seq::range('1..Inf')->takeWhile(fn($i) => $i < 100) creates [1, 2, 3, ..., 99]
     * Seq::infinite()->filter(fn($i) => $i % 2 === 0)->map(fn($i) => $i * $i)->takeWhile(fn($i) => $i < 25)->toArray(); creates [4, 16]
     *
     * @param callable $callable (Item, Key) => bool
     * @return ISeq
     */
    public function takeWhile(callable $callable): self;

    /**
     * Seq takes up to n items from sequence.
     * Note: If there is not enough items, it will return all items.
     *
     * @return ISeq
     */
    public function takeUpTo(int $limit): self;

    /**
     * @param callable $reducer (total:mixed,value:mixed,index:mixed,collection:ISeq):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce(callable $reducer, $initialValue = null);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return ISeq
     */
    public function filter(callable $callback);

    /**
     * @param mixed $value
     */
    public function contains($value): bool;

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     */
    public function containsBy(callable $callback): bool;

    public function isEmpty(): bool;

    /** @return ISeq */
    public function clear();

    /** @deprecated Seq does not have a mutable variant */
    public function asMutable();

    public function count(): int;

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return ISeq
     */
    public function map(callable $callback);

    /** @param callable $callback (value:mixed,index:mixed):void */
    public function each(callable $callback): void;

    /**
     * Applies the given function to each element of the sequence and concatenates all the results
     *
     * Note: if mapping is not necessary, you can use just concat instead
     * @see ISeq::concat()
     *
     * @param callable $callback (value:mixed,index:mixed):iterable
     */
    public function collect(callable $callback): self;

    /**
     * Combines the given iterable-of-iterables as a single concatenated iterable
     *
     * Note: map->concat could be replaced by collect
     * @see ISeq::collect()
     *
     * @example Seq::from([ [1,2,3], [4,5,6] ])->concat()->toArray() // [1,2,3,4,5,6]
     */
    public function concat(): self;

    public function implode(string $glue): string;
}
