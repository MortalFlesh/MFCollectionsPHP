<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Assertion;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Helper\Callback;
use MF\Collection\Helper\Collection;

/**
 * @phpstan-import-type TIndex from IList
 * @phpstan-template TValue
 *
 * @phpstan-implements IList<TValue>
 */
readonly class ListCollection implements IList
{
    /**
     * @phpstan-template T
     * @phpstan-param IList<T|iterable<T>> $list
     * @phpstan-return IList<T>
     */
    public static function concatList(IList $list): IList
    {
        $values = [];
        foreach ($list as $items) {
            if (is_iterable($items)) {
                foreach ($items as $item) {
                    $values[] = $item;
                }
            } else {
                $values[] = $items;
            }
        }

        return new static($values);
    }

    /**
     * @phpstan-param TValue $values
     * @phpstan-return IList<TValue>
     */
    public static function of(mixed ...$values): IList
    {
        return static::from($values);
    }

    /**
     * @phpstan-param iterable<mixed, TValue> $source
     * @phpstan-return IList<TValue>
     */
    public static function from(iterable $source): IList
    {
        $listArray = [];

        foreach ($source as $value) {
            $listArray[] = $value;
        }

        return new static($listArray);
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param iterable<int|string, T> $source
     * @phpstan-param callable(T, int|string): TValue $creator
     * @phpstan-return IList<TValue>
     */
    public static function create(iterable $source, callable $creator): IList
    {
        $listArray = [];
        $creator = Callback::curry($creator);

        foreach ($source as $index => $value) {
            $listArray[] = $creator($value, $index);
        }

        return new static($listArray);
    }

    /** @phpstan-param array<TIndex, TValue> $listArray */
    public function __construct(private array $listArray = [])
    {
    }

    public function count(): int
    {
        return count($this->listArray);
    }

    /** @phpstan-return \Traversable<TIndex, TValue> */
    public function getIterator(): \Traversable
    {
        yield from $this->listArray;
    }

    public function isEmpty(): bool
    {
        return empty($this->listArray);
    }

    /** @phpstan-param TValue $value */
    public function contains(mixed $value): bool
    {
        return $this->find($value) !== false;
    }

    protected function find(mixed $value): int|false
    {
        return array_search($value, $this->listArray, true);
    }

    /** @phpstan-param callable(TValue, TIndex=): bool $callback */
    public function containsBy(callable $callback): bool
    {
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            if ($callback($v, $i) === true) {
                return true;
            }
        }

        return false;
    }

    /** @phpstan-return array<TIndex, TValue> */
    public function toArray(): array
    {
        return Collection::immutableToArray($this);
    }

    /** @param callable(TValue, TIndex=): void $callback */
    public function each(callable $callback): void
    {
        $callback = Callback::curry($callback);

        foreach ($this as $i => $value) {
            $callback($value, $i);
        }
    }

    /**
     * Tests if all elements of the collection satisfy the given predicate.
     *
     * @phpstan-param callable(TValue, TIndex=): bool $predicate
     */
    public function forAll(callable $predicate): bool
    {
        $predicate = Callback::curry($predicate);

        foreach ($this as $i => $v) {
            if ($predicate($v, $i) !== true) {
                return false;
            }
        }

        return true;
    }

    public function implode(string $glue): string
    {
        return implode($glue, $this->listArray);
    }

    /** @phpstan-return TValue|null */
    public function first(): mixed
    {
        return empty($listArray = $this->listArray)
            ? null
            : reset($listArray);
    }

    /**
     * @phpstan-param callable(TValue, TIndex=): bool $callback
     * @phpstan-return TValue|null
     */
    public function firstBy(callable $callback): mixed
    {
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            if ($callback($v, $i)) {
                return $v;
            }
        }

        return null;
    }

    /** @phpstan-return TValue|null */
    public function last(): mixed
    {
        $list = $this->listArray;

        return array_pop($list);
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function add(mixed $value): IList
    {
        $listArray = $this->listArray;
        $listArray[] = $value;

        return new static($listArray);
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function unshift(mixed $value): IList
    {
        $listArray = $this->listArray;
        array_unshift($listArray, $value);

        return new static($listArray);
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function removeFirst(mixed $value): IList
    {
        $index = $this->find($value);

        return $index !== false
            ? $this->removeIndex($index)
            : $this;
    }

    /** @phpstan-return IList<TValue> */
    private function removeIndex(int $index): IList
    {
        $listArray = $this->listArray;
        unset($listArray[$index]);

        return new static($listArray);
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return IList<TValue>
     */
    public function removeAll(mixed $value): IList
    {
        return $this->filter(fn (mixed $val): bool => $value !== $val);
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return IList<T>
     */
    public function map(callable $callback): IList
    {
        $listArray = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            $listArray[$i] = $callback($v);
        }

        return new static($listArray);
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TIndex): T $callback
     * @phpstan-return IList<T>
     */
    public function mapi(callable $callback): IList
    {
        $listArray = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            $listArray[$i] = $callback($v, $i);
        }

        return new static($listArray);
    }

    /**
     * @phpstan-param callable(TValue, TIndex=): bool $callback
     * @phpstan-return IList<TValue>
     */
    public function filter(callable $callback): IList
    {
        $listArray = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            if ($callback($v, $i) === true) {
                $listArray[] = $v;
            }
        }

        return new static($listArray);
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TIndex=): (T|null) $callback
     * @phpstan-return IList<T>
     */
    public function choose(callable $callback): IList
    {
        $listArray = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            if (($value = $callback($v, $i)) !== null) {
                $listArray[] = $value;
            }
        }

        return new static($listArray);
    }

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State, TValue, TIndex=, IList<TValue>=): State $reducer
     * @phpstan-param State $initialValue
     * @phpstan-return State
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $reducer = Callback::curry($reducer);
        $state = $initialValue;

        foreach ($this as $i => $value) {
            $state = $reducer($state, $value, $i, $this);
        }

        return $state;
    }

    /** @phpstan-return IList<TValue> */
    public function sort(): IList
    {
        $sorted = $this->listArray;
        sort($sorted);

        return static::from($sorted);
    }

    /** @phpstan-return IList<TValue> */
    public function sortDescending(): IList
    {
        $sorted = $this->listArray;
        rsort($sorted);

        return static::from($sorted);
    }

    /**
     * @phpstan-param callable(TValue, TValue): (string|int|float) $callback
     * @phpstan-return IList<TValue>
     */
    public function sortBy(callable $callback): IList
    {
        $callback = Callback::curry($callback);
        $sorted = $this->listArray;

        usort(
            $sorted,
            fn (mixed $a, mixed $b): int => $callback($a) <=> $callback($b)
        );

        return static::from($sorted);
    }

    /**
     * @phpstan-param callable(TValue, TIndex=): (string|int|float) $callback
     * @phpstan-return IList<TValue>
     */
    public function sortByDescending(callable $callback): IList
    {
        $callback = Callback::curry($callback);
        $sorted = $this->listArray;

        usort(
            $sorted,
            fn (mixed $a, mixed $b): int => $callback($b) <=> $callback($a)
        );

        return static::from($sorted);
    }

    /**
     * Keeps only unique values inside the list.
     *
     * @phpstan-return IList<TValue>
     */
    public function unique(): IList
    {
        return static::from(array_unique($this->listArray));
    }

    /**
     * Keeps only unique values by a given callback inside the list.
     *
     * @phpstan-template Unique
     *
     * @phpstan-param callable(TValue, TIndex=): Unique $callback
     * @phpstan-return IList<TValue>
     */
    public function uniqueBy(callable $callback): IList
    {
        $callback = Callback::curry($callback);

        $uniques = [];
        $list = [];
        foreach ($this as $i => $value) {
            $unique = $callback($value, $i);

            if (!in_array($unique, $uniques, true)) {
                $list[] = $value;
            }

            $uniques[] = $unique;
        }

        return static::from($list);
    }

    /**
     * Sort all items in a reverse order.
     *
     * @phpstan-return IList<TValue>
     */
    public function reverse(): IList
    {
        return static::from(array_reverse($this->listArray));
    }

    public function sum(): int|float
    {
        return array_sum($this->listArray);
    }

    /** @phpstan-param callable(TValue, TIndex=): (int|float) $callback */
    public function sumBy(callable $callback): int|float
    {
        $callback = Callback::curry($callback);

        return $this->reduce(
            fn (int|float $sum, mixed $value, int $i): int|float => $sum + $callback($value, $i),
            0,
        );
    }

    /** @phpstan-return IList<TValue> */
    public function clear(): IList
    {
        return new static();
    }

    /**
     * @phpstan-param IList<TValue> $list
     * @phpstan-return IList<TValue>
     */
    public function append(IList $list): IList
    {
        return static::of(...$this, ...$list);
    }

    /**
     * Divides the list into chunks of size at most chunkSize.
     *
     * @phpstan-param int<1, max> $size
     * @phpstan-return IList<IList<TValue>>
     *
     * @throws InvalidArgumentException
     */
    public function chunkBySize(int $size): IList
    {
        Assertion::greaterThan($size, 0);

        return Seq::init(function () use ($size) {
            $currentChunkSize = 0;
            $chunk = [];

            foreach ($this as $v) {
                if ($currentChunkSize < $size) {
                    $chunk[] = $v;
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
        })
            ->toList();
    }

    /**
     * Splits the list into at most count chunks.
     *
     * @phpstan-param int<1, max> $count
     * @phpstan-return IList<IList<TValue>>
     *
     * @throws InvalidArgumentException
     */
    public function splitInto(int $count): IList
    {
        Assertion::greaterThan($count, 0);

        if ($count === 1) {
            return static::of($this);
        }

        $itemsCount = $this->count();

        $mediumSize = $itemsCount / $count;
        /** @phpstan-var int<1, max> $idealSize */
        $idealSize = (int) ceil($mediumSize);
        $minimalSize = (int) floor($mediumSize);

        if ($itemsCount <= $count || $idealSize === $minimalSize || $count === 2) {
            return $this->chunkBySize($idealSize);
        }

        return Seq::init(function () use ($itemsCount, $minimalSize, $idealSize, $count) {
            $offset = 0;
            $splitBy = $idealSize;

            while ($count > 0) {
                $slice = array_slice($this->listArray, $offset, $splitBy);

                yield static::from($slice);

                $offset += count($slice);
                $count--;

                $rest = $itemsCount - $offset;
                if ($splitBy === $idealSize && ($rest % $minimalSize === 0)) {
                    $splitBy = $minimalSize;
                }
            }
        })
            ->toList();
    }

    /**
     * For each element of the list, applies the given function.
     * Concatenates all the results and return the combined list.
     *
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): iterable<T> $callback
     * @phpstan-return IList<T>
     */
    public function collect(callable $callback): IList
    {
        $callback = Callback::curry($callback);

        /** @phsptan-var IList<iterable<T>> $collected */
        $collected = $this->map(fn (mixed $v): iterable => $callback($v));
        /** @phpstan-var IList<T> $concatenated */
        $concatenated = $collected->concat();

        return $concatenated;
    }

    /**
     * Returns a new list that contains the elements of each the lists in order.
     *
     * @phpstan-return IList<TValue>
     */
    public function concat(): IList
    {
        return static::concatList($this);
    }

    /**
     * @phpstan-template TKey of int|string
     *
     * @phpstan-param callable(TValue, TIndex=): TKey $callback
     * @phpstan-return IList<KVPair<TKey, int>>
     */
    public function countBy(callable $callback): IList
    {
        $callback = Callback::curry($callback);

        /** @phpstan-var IMap<TKey, int> $counts */
        $counts = $this->reduce(
            function (IMap $counts, mixed $value, int $i) use ($callback) {
                $key = $callback($value, $i);

                return $counts->containsKey($key)
                    ? $counts->set($key, $counts->get($key) + 1)
                    : $counts->set($key, 1);
            },
            new Map(),
        );

        return $counts->pairs();
    }

    /**
     * @phpstan-template TGroup of int|string
     *
     * @phpstan-param callable(TValue): TGroup $callback
     * @phpstan-return IList<KVPair<TGroup, IList<TValue>>>
     */
    public function groupBy(callable $callback): IList
    {
        $callback = Callback::curry($callback);

        /** @phpstan-var IMap<TGroup, IList<TValue>> $groups */
        $groups = $this->reduce(
            function (IMap $groups, mixed $value) use ($callback) {
                $groupKey = $callback($value);

                return $groups->set(
                    $groupKey,
                    $groups->containsKey($groupKey)
                        ? $groups->get($groupKey)->add($value)
                        : static::of($value),
                );
            },
            new Map(),
        );

        return $groups->pairs();
    }

    /** @phpstan-return TValue|null */
    public function min(): mixed
    {
        /** @phpstan-var TValue $min */
        $min = min($this->listArray) ?: null;

        return $min;
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return TValue|null
     */
    public function minBy(callable $callback): mixed
    {
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

    /** @phpstan-return TValue|null */
    public function max(): mixed
    {
        /** @phpstan-var TValue $max */
        $max = max($this->listArray) ?: null;

        return $max;
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     * @phpstan-return TValue|null
     */
    public function maxBy(callable $callback): mixed
    {
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

    /** @phpstan-return \MF\Collection\Mutable\Generic\IList<TValue> */
    public function asMutable(): \MF\Collection\Mutable\Generic\IList
    {
        /** @phpstan-var \MF\Collection\Mutable\Generic\IList<TValue> $mutableList */
        $mutableList = \MF\Collection\Mutable\Generic\ListCollection::from($this);

        return $mutableList;
    }

    /** @phpstan-return ISeq<TValue> */
    public function toSeq(): ISeq
    {
        /** @phpstan-var ISeq<TValue> $seq */
        $seq = Seq::from($this->listArray);

        return $seq;
    }
}
