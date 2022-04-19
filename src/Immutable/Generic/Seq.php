<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Assertion;
use MF\Collection\Exception\OutOfBoundsException;
use MF\Collection\Exception\OutOfRangeException;
use MF\Collection\Helper\Callback;
use MF\Collection\Helper\SeqModifier;
use MF\Collection\Range;

/**
 * @phpstan-import-type TIndex from ISeq
 * @phpstan-template TValue
 * @phpstan-type DataSource iterable<TValue>|\Closure(): iterable<TValue>
 *
 * @phpstan-import-type RangeDefinition from Range
 *
 * @phpstan-implements ISeq<TValue>
 */
class Seq implements ISeq
{
    /**
     * @see SeqModifier
     * @phpstan-var array<int, array{0: SeqModifier, 1: mixed}>
     */
    protected array $modifiers;

    private bool $isInfinite = false;

    /**
     * @phpstan-template T
     * @phpstan-param ISeq<T|iterable<T>> $seq
     * @phpstan-return ISeq<T>
     */
    public static function concatSeq(ISeq $seq): ISeq
    {
        return static::init(function () use ($seq) {
            foreach ($seq as $v) {
                if (is_iterable($v)) {
                    yield from $v;
                } else {
                    yield $v;
                }
            }
        });
    }

    /** @phpstan-param DataSource $iterable */
    public function __construct(private readonly iterable|\Closure $iterable)
    {
        $this->modifiers = [];
    }

    /**
     * Seq::of(1, 2, 3)
     * Seq::of(...$array, ...$array2)
     *
     * @phpstan-param TValue $args
     * @phpstan-return ISeq<TValue>
     */
    public static function of(mixed ...$args): ISeq
    {
        return new static($args);
    }

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
    public static function forDo(string|array $range, callable $callable): ISeq
    {
        [$start, $end, $step] = Range::parse($range);

        if ($end === self::INFINITE) {
            $seq = new static(
                function () use ($start, $step, $callable) {
                    $callable = Callback::curry($callable);

                    for ($i = $start; true; $i += $step) {
                        $item = $callable((int) $i);

                        if (is_iterable($item)) {
                            yield from $item;
                        } else {
                            yield $item;
                        }
                    }
                },
            );

            return $seq->setIsInfinite();
        }

        return new static(
            function () use ($callable, $step, $end, $start) {
                $callable = Callback::curry($callable);

                foreach (range($start, $end, $step) as $i) {
                    $item = $callable((int) $i);

                    if (is_iterable($item)) {
                        yield from $item;
                    } else {
                        yield $item;
                    }
                }
            },
        );
    }

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State): array{0: State, 1: State|null} $callable
     * @phpstan-param State $initialValue
     * @phpstan-return ISeq<State>
     */
    public static function unfold(callable $callable, mixed $initialValue): ISeq
    {
        return new static(
            function () use ($callable, $initialValue) {
                $callable = Callback::curry($callable);
                $next = $initialValue;

                do {
                    [$total, $next] = $callable($next);
                    if (!$next) {
                        return;
                    }

                    yield $total;
                } while (true);
            },
        );
    }

    /**
     * Alias for empty sequence
     * Seq::create([])
     *
     * @phpstan-return ISeq<TValue>
     */
    public static function createEmpty(): ISeq
    {
        return static::from([]);
    }

    /**
     * Alias for infinite range 1..Inf
     * Seq::range('1..Inf')
     *
     * @phpstan-return ISeq<int>
     */
    public static function infinite(): ISeq
    {
        return static::range([1, self::INFINITE]);
    }

    /**
     * @phpstan-param iterable<mixed, TValue> $source
     * @phpstan-return ISeq<TValue>
     */
    public static function from(iterable $source): ISeq
    {
        return new static($source);
    }

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
    public static function create(iterable $iterable, callable $creator): ISeq
    {
        return new static(function () use ($iterable, $creator) {
            $creator = Callback::curry($creator);

            foreach ($iterable as $value) {
                $item = $creator($value);

                if (is_iterable($item)) {
                    yield from $item;
                } else {
                    yield $item;
                }
            }
        });
    }

    /**
     * Seq::range([1, 10])
     * Seq::range([1, 10, 100])
     * Seq::range('1..10')
     * Seq::range('1..10..100')
     *
     * @phpstan-param RangeDefinition $range
     * @phpstan-return ISeq<int>
     */
    public static function range(string|array $range): ISeq
    {
        [$start, $end, $step] = Range::parse($range);

        if ($end === self::INFINITE) {
            $seq = new static(
                function () use ($start, $step) {
                    for ($i = $start; true; $i += $step) {
                        yield $i;
                    }
                },
            );

            return $seq->setIsInfinite();
        }

        return new static(range($start, $end, $step));
    }

    /** @phpstan-return ISeq<TValue> */
    private function setIsInfinite(bool $isInfinite = true): ISeq
    {
        $this->isInfinite = $isInfinite;

        return $this;
    }

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
    public static function init(iterable|callable $iterable): ISeq
    {
        return new static($iterable);
    }

    /** @phpstan-return \Traversable<TValue> */
    public function getIterator(): \Traversable
    {
        $counter = 0;
        $strictLimit = null;

        // this `if` has to be here, because I do not know other way of iterating callable|iterable, or abstracting that
        if (is_callable($this->iterable)) {
            foreach (call_user_func($this->iterable) as $index => $value) {
                foreach ($this->modifiers as $modifierKey => [$type, $modifier]) {
                    if ($type === SeqModifier::Map) {
                        $value = $this->mapValue($modifier, $value);
                    } elseif ($type === SeqModifier::Mapi) {
                        $value = $this->mapiValue($modifier, $value, $index);
                    } elseif ($type === SeqModifier::Filter && !$modifier($value, $index)) {
                        continue 2;
                    } elseif ($type === SeqModifier::Take) {
                        $strictLimit = $modifier;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === SeqModifier::TakeUpTo) {
                        $strictLimit = null;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === SeqModifier::TakeWhile) {
                        $strictLimit = null;
                        if (!call_user_func($modifier, $value, $index)) {
                            break 2;
                        }
                    }
                }

                $counter++;
                yield $index => $value;
            }
        } else {
            foreach ($this->iterable as $index => $value) {
                foreach ($this->modifiers as [$type, $modifier]) {
                    if ($type === SeqModifier::Map) {
                        $value = $this->mapValue($modifier, $value);
                    } elseif ($type === SeqModifier::Mapi) {
                        $value = $this->mapiValue($modifier, $value, $index);
                    } elseif ($type === SeqModifier::Filter && !$modifier($value, $index)) {
                        continue 2;
                    } elseif ($type === SeqModifier::Take) {
                        $strictLimit = $modifier;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === SeqModifier::TakeUpTo) {
                        $strictLimit = null;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === SeqModifier::TakeWhile) {
                        $strictLimit = null;
                        if (!call_user_func($modifier, $value, $index)) {
                            break 2;
                        }
                    }
                }

                $counter++;
                yield $index => $value;
            }
        }

        if ($strictLimit !== null && $counter < $strictLimit) {
            throw $this->createOutOfRange((int) $strictLimit, $counter);
        }

        $this->modifiers = [];
    }

    private function createOutOfRange(int $strictLimit, int $count): OutOfRangeException
    {
        return new OutOfRangeException(
            sprintf('Seq does not have %d items to take, it only has %d items.', $strictLimit, $count),
        );
    }

    private function mapValue(callable $modifier, mixed $value): mixed
    {
        $value = $modifier($value);
        Assertion::notIsInstanceOf($value, \Generator::class, 'Mapping must not generate new values.');

        return $value;
    }

    private function mapiValue(callable $modifier, mixed $value, int $index): mixed
    {
        $value = $modifier($value, $index);
        Assertion::notIsInstanceOf($value, \Generator::class, 'Mapping must not generate new values.');

        return $value;
    }

    /**
     * Seq takes exactly n items from sequence.
     * Note: If there is not enough items, it will throw an exception.
     *
     * @phpstan-return ISeq<TValue>
     * @throws \OutOfRangeException
     */
    public function take(int $limit): ISeq
    {
        return $this->clone()
            ->addModifier(SeqModifier::Take, $limit)
            ->setIsInfinite(false);
    }

    private function clone(): static
    {
        return clone ($this);
    }

    /**
     * Seq takes up to n items from sequence.
     * Note: If there is not enough items, it will return all items.
     *
     * @phpstan-return ISeq<TValue>
     */
    public function takeUpTo(int $limit): ISeq
    {
        return $this->clone()
            ->addModifier(SeqModifier::TakeUpTo, $limit)
            ->setIsInfinite(false);
    }

    /**
     * Seq takes items till callable will return false.
     * Note: Item and Key given to callable will be mapped by given mapping.
     *
     * @example
     * Seq::range('1..Inf')->takeWhile(fn($i) => $i < 100) creates [1, 2, 3, ..., 99]
     * Seq::infinite()->filter(fn($i) => $i % 2 === 0)->map(fn($i) => $i * $i)->takeWhile(fn($i) => $i < 25)->toArray(); creates [4, 16]
     *
     * @phpstan-template Key
     * @phpstan-template Item
     *
     * @phpstan-param callable(Item, Key): bool $callable
     * @phpstan-return ISeq<TValue>
     */
    public function takeWhile(callable $callable): ISeq
    {
        return $this->clone()
            ->addModifier(SeqModifier::TakeWhile, Callback::curry($callable))
            ->setIsInfinite(false);
    }

    public function skip(int $limit): ISeq
    {
        $seq = $this->clone();

        return static::init(function () use ($limit, $seq) {
            $skipped = 0;
            foreach ($seq as $value) {
                if ($skipped++ < $limit) {
                    continue;
                }

                yield $value;
            }
        });
    }

    public function skipWhile(callable $callable): ISeq
    {
        $seq = $this->clone();

        return static::init(function () use ($callable, $seq) {
            $callable = Callback::curry($callable);
            $take = false;

            foreach ($seq as $i => $value) {
                if (!$take && $callable($value, $i) === true) {
                    continue;
                }
                $take = true;

                yield $value;
            }
        });
    }

    /** @phpstan-return TValue[] */
    public function toArray(): array
    {
        $array = [];
        foreach ($this as $value) {
            /** @var TValue $normalizedValue */
            $normalizedValue = $value instanceof \MF\Collection\Mutable\Generic\ICollection || $value instanceof ICollection
                ? $value->toArray()
                : $value;

            $array[] = $normalizedValue;
        }

        return $array;
    }

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State, TValue, TIndex=, ISeq<TValue>=): State $reducer
     * @phpstan-param State $initialValue
     * @phpstan-return State
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $this->assertFinite('reduce');

        $reducer = Callback::curry($reducer);
        $state = $initialValue;

        $seq = $this->clone();

        foreach ($seq as $index => $value) {
            $state = $reducer($state, $value, $index, $seq);
        }

        return $state;
    }

    private function assertFinite(string $intent): void
    {
        if ($this->isInfinite) {
            throw new OutOfBoundsException(sprintf('It is not possible to %s infinite seq.', $intent));
        }
    }

    /**
     * @phpstan-param callable(TValue, int): bool $callback
     * @phpstan-return ISeq<TValue>
     */
    public function filter(callable $callback): ISeq
    {
        return $this->clone()
            ->addModifier(SeqModifier::Filter, Callback::curry($callback))
            ->setIsInfinite(false);
    }

    private function addModifier(SeqModifier $type, mixed $modifier): static
    {
        $this->modifiers[] = [$type, $modifier];

        return $this;
    }

    /** @phpstan-param TValue $value */
    public function contains(mixed $value): bool
    {
        foreach ($this as $v) {
            if ($v === $value) {
                return true;
            }
        }

        return false;
    }

    /** @phpstan-param callable(TValue, int): bool $callback */
    public function containsBy(callable $callback): bool
    {
        $callback = Callback::curry($callback);

        foreach ($this as $i => $value) {
            if ($callback($value, $i) === true) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        foreach ($this as $v) {
            return false;
        }

        return true;
    }

    /** @phpstan-return ISeq<TValue> */
    public function clear(): ISeq
    {
        return static::createEmpty();
    }

    public function count(): int
    {
        $this->assertFinite('count');

        return count($this->toArray());
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, int): T $callback
     * @phpstan-return ISeq<T>
     */
    public function map(callable $callback): ISeq
    {
        return $this->clone()
            ->addModifier(SeqModifier::Map, Callback::curry($callback));
    }

    public function mapi(callable $callback): ISeq
    {
        return $this->clone()
            ->addModifier(SeqModifier::Mapi, Callback::curry($callback));
    }

    /** @phpstan-param callable(TValue, int): void $callback */
    public function each(callable $callback): void
    {
        $callback = Callback::curry($callback);

        foreach ($this as $i => $value) {
            $callback($value, $i);
        }
    }

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
    public function collect(callable $callback): ISeq
    {
        /** @phsptan-var ISeq<iterable<T>> $collected */
        $collected = $this->map($callback);
        /** @phpstan-var ISeq<T> $concatenated */
        $concatenated = $collected->concat();

        return $concatenated;
    }

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
    public function concat(): ISeq
    {
        return static::concatSeq($this);
    }

    public function implode(string $glue): string
    {
        return implode($glue, $this->toArray());
    }

    public function toList(): IList
    {
        $this->assertFinite('toList');

        /** @phpstan-var IList<TValue> $list */
        $list = ListCollection::from($this->toArray());

        return $list;
    }

    public function forAll(callable $predicate): bool
    {
        $predicate = Callback::curry($predicate);

        foreach ($this as $i => $value) {
            if ($predicate($value, $i) === false) {
                return false;
            }
        }

        return true;
    }

    public function sort(): ISeq
    {
        $this->assertFinite('sort');

        $seq = $this->clone();

        return static::init(function () use ($seq) {
            $items = $seq->toArray();
            sort($items);

            yield from $items;
        });
    }

    public function sortDescending(): ISeq
    {
        $this->assertFinite('sortDescending');

        $seq = $this->clone();

        return static::init(function () use ($seq) {
            $items = $seq->toArray();
            rsort($items);

            yield from $items;
        });
    }

    public function sortBy(callable $callback): ISeq
    {
        $this->assertFinite('sortBy');

        $seq = $this->clone();

        return static::init(function () use ($seq, $callback) {
            $items = $seq->toArray();
            $callback = Callback::curry($callback);

            usort(
                $items,
                fn (mixed $a, mixed $b): int => $callback($a) <=> $callback($b)
            );

            yield from $items;
        });
    }

    public function sortByDescending(callable $callback): ISeq
    {
        $this->assertFinite('sortByDescending');

        $seq = $this->clone();

        return static::init(function () use ($seq, $callback) {
            $items = $seq->toArray();
            $callback = Callback::curry($callback);

            usort(
                $items,
                fn (mixed $a, mixed $b): int => $callback($b) <=> $callback($a)
            );

            yield from $items;
        });
    }

    public function unique(): ISeq
    {
        $this->assertFinite('unique');

        $seq = $this->clone();

        return static::init(function () use ($seq) {
            yield from array_unique($seq->toArray());
        });
    }

    public function uniqueBy(callable $callback): ISeq
    {
        $this->assertFinite('uniqueBy');

        $seq = $this->clone();

        return static::init(function () use ($callback, $seq) {
            $callback = Callback::curry($callback);

            $uniques = [];
            foreach ($seq as $value) {
                $unique = $callback($value);

                if (!in_array($unique, $uniques, true)) {
                    yield $value;
                }

                $uniques[] = $unique;
            }
        });
    }

    public function reverse(): ISeq
    {
        $this->assertFinite('reverse');

        $seq = $this->clone();

        return static::init(function () use ($seq) {
            $array = $seq->toArray();
            $index = count($array);

            while ($index) {
                yield $array[--$index];
            }
        });
    }

    public function sum(): int|float
    {
        return $this->reduce(
            fn (int|float $sum, mixed $value) => $sum + $value,
            0,
        );
    }

    public function sumBy(callable $callback): int|float
    {
        $callback = Callback::curry($callback);

        return $this->reduce(
            fn (int|float $sum, mixed $value) => $sum + $callback($value),
            0,
        );
    }

    public function append(ISeq $seq): ISeq
    {
        $seqA = $this->clone();
        $seqB = clone $seq;

        $appended = static::init(function () use ($seqA, $seqB) {
            yield from $seqA;
            yield from $seqB;
        });

        $isInfinite = $seqB instanceof self
            ? $seqA->isInfinite || $seqB->isInfinite
            : $seqA->isInfinite;

        return $appended instanceof self
            ? $appended->setIsInfinite($isInfinite)
            : $appended;
    }

    public function chunkBySize(int $size): ISeq
    {
        Assertion::greaterThan($size, 0);

        $seq = $this->clone();

        return static::init(function () use ($size, $seq) {
            $currentChunkSize = 0;
            $chunk = [];

            foreach ($seq as $value) {
                if ($currentChunkSize < $size) {
                    $chunk[] = $value;
                    $currentChunkSize++;
                }

                if ($currentChunkSize === $size) {
                    yield static::from($chunk);

                    $currentChunkSize = 0;
                    $chunk = [];
                }
            }

            if ($currentChunkSize > 0) {
                yield static::from($chunk);
            }
        });
    }

    public function splitInto(int $count): ISeq
    {
        Assertion::greaterThan($count, 0);

        $seq = $this->clone();

        if ($count === 1) {
            return static::of($seq);
        }

        $this->assertFinite('splitInto');

        return static::init(function () use ($count, $seq) {
            $array = $seq->toArray();

            $itemsCount = count($array);

            $mediumSize = $itemsCount / $count;
            $idealSize = (int) ceil($mediumSize);
            $minimalSize = (int) floor($mediumSize);

            if ($itemsCount <= $count || $idealSize === $minimalSize || $count === 2) {
                yield from $seq->chunkBySize($idealSize);

                return;
            }

            $offset = 0;
            $splitBy = $idealSize;

            while ($count > 0) {
                $slice = array_slice($array, $offset, $splitBy);

                yield static::from($slice);

                $offset += count($slice);
                $count--;

                $rest = $itemsCount - $offset;
                if ($splitBy === $idealSize && ($rest % $minimalSize === 0)) {
                    $splitBy = $minimalSize;
                }
            }
        });
    }

    /**
     * @phpstan-template TKey of int|string
     *
     * @phpstan-param callable(TValue): TKey $callback
     * @phpstan-return ISeq<KVPair<TKey, int>>
     */
    public function countBy(callable $callback): ISeq
    {
        $seq = $this->clone();

        return static::init(function () use ($callback, $seq) {
            $callback = Callback::curry($callback);

            /** @var IMap<TKey, int> $counts */
            $counts = $seq->reduce(
                function (IMap $counts, mixed $value) use ($callback) {
                    $key = $callback($value);

                    return $counts->containsKey($key)
                        ? $counts->set($key, $counts->get($key) + 1)
                        : $counts->set($key, 1);
                },
                new Map(),
            );

            yield from $counts->pairs();
        });
    }

    /**
     * @phpstan-template TGroup of int|string
     *
     * @phpstan-param callable(TValue): TGroup $callback
     * @phpstan-return ISeq<KVPair<TGroup, ISeq<TValue>>>
     */
    public function groupBy(callable $callback): ISeq
    {
        $seq = $this->clone();

        return static::init(function () use ($callback, $seq) {
            $callback = Callback::curry($callback);

            /** @var IMap<TGroup, IList<TValue>> $groups */
            $groups = $seq->reduce(
                function (IMap $groups, mixed $value) use ($callback) {
                    $groupKey = $callback($value);

                    return $groups->set(
                        $groupKey,
                        $groups->containsKey($groupKey)
                            ? $groups->get($groupKey)->append(static::of($value))
                            : static::of($value),
                    );
                },
                new Map(),
            );

            yield from $groups->pairs();
        });
    }

    public function min(): mixed
    {
        $this->assertFinite('min');
        $array = $this->toArray();

        /** @phpstan-var TValue $min */
        $min = min($array) ?: null;

        return $min;
    }

    public function minBy(callable $callback): mixed
    {
        $this->assertFinite('minBy');

        $min = null;
        $currentMinResult = null;
        $callback = Callback::curry($callback);

        foreach ($this as $v) {
            $minResult = $callback($v);

            if ($currentMinResult === null || ($minResult < $currentMinResult)) {
                $min = $v;
                $currentMinResult = $minResult;
            }
        }

        return $min;
    }

    public function max(): mixed
    {
        $this->assertFinite('max');
        $array = $this->toArray();

        /** @phpstan-var TValue $max */
        $max = max($array) ?: null;

        return $max;
    }

    public function maxBy(callable $callback): mixed
    {
        $this->assertFinite('maxBy');

        $max = null;
        $currentMaxResult = null;
        $callback = Callback::curry($callback);

        foreach ($this as $v) {
            $maxResult = $callback($v);

            if ($currentMaxResult === null || ($maxResult > $currentMaxResult)) {
                $max = $v;
                $currentMaxResult = $maxResult;
            }
        }

        return $max;
    }
}
