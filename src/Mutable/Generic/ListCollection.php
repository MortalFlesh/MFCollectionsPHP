<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Helper\Callback;
use MF\Collection\Helper\Collection;
use MF\Collection\Immutable\Generic\IMap;
use MF\Collection\Immutable\Generic\ISeq;
use MF\Collection\Immutable\Generic\KVPair;
use MF\Collection\Immutable\Generic\Map;
use MF\Collection\Immutable\Generic\Seq;

/**
 * @phpstan-import-type TIndex from IList
 * @phpstan-template TValue
 *
 * @phpstan-implements IList<TValue>
 */
class ListCollection implements IList
{
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
        $list = new static();

        foreach ($source as $value) {
            $list->listArray[] = $value;
        }

        return $list;
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
        $list = new static();
        $creator = Callback::curry($creator);

        foreach ($source as $index => $value) {
            $list->listArray[] = $creator($value, $index);
        }

        return $list;
    }

    /** @phpstan-param array<TIndex, TValue> $listArray */
    public function __construct(private array $listArray = [])
    {
        $this->listArray = array_values($this->listArray);
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
        return Collection::mutableToArray($this);
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
        return empty($this->listArray)
            ? null
            : reset($this->listArray);
    }

    /**
     * @phpstan-param callable(TValue, TIndex): bool $callback
     * @phpstan-return TValue|null
     */
    public function firstBy(callable $callback): mixed
    {
        $callback = Callback::curry($callback);

        foreach ($this->listArray as $i => $v) {
            if ($callback($v, $i) === true) {
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

    /** @phpstan-return TValue|null */
    public function shift(): mixed
    {
        return array_shift($this->listArray);
    }

    /** @phpstan-return TValue|null */
    public function pop(): mixed
    {
        return array_pop($this->listArray);
    }

    /** @phpstan-param TValue $value */
    public function add(mixed $value): void
    {
        $this->listArray[] = $value;
    }

    /** @phpstan-param TValue $value */
    public function unshift(mixed $value): void
    {
        array_unshift($this->listArray, $value);
    }

    /** @phpstan-param TValue $value */
    public function removeFirst(mixed $value): void
    {
        $index = $this->find($value);

        if ($index !== false) {
            $this->removeIndex($index);
        }
    }

    private function find(mixed $value): int|false
    {
        return array_search($value, $this->listArray, true);
    }

    private function removeIndex(int $index): void
    {
        unset($this->listArray[$index]);

        $this->normalize();
    }

    private function normalize(): void
    {
        $list = $this->listArray;
        $this->listArray = [];

        foreach ($list as $value) {
            $this->listArray[] = $value;
        }
    }

    /** @phpstan-param TValue $value */
    public function removeAll(mixed $value): void
    {
        $this->filter(fn (mixed $val): bool => $value !== $val);
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue): T $callback
     */
    public function map(callable $callback): void
    {
        $list = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            $list[$i] = $callback($v);
        }

        /** @phpstan-var array<TIndex, TValue> $list */
        $this->listArray = $list;
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TIndex): T $callback
     */
    public function mapi(callable $callback): void
    {
        $list = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            $list[$i] = $callback($v, $i);
        }

        /** @phpstan-var array<TIndex, TValue> $list */
        $this->listArray = $list;
    }

    /** @phpstan-param callable(TValue, TIndex=): bool $callback */
    public function filter(callable $callback): void
    {
        $list = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $v) {
            if ($callback($v, $i) === true) {
                $list[] = $v;
            }
        }

        $this->listArray = $list;
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
        $state = $initialValue;
        $reducer = Callback::curry($reducer);

        foreach ($this as $i => $value) {
            $state = $reducer($state, $value, $i, $this);
        }

        return $state;
    }

    public function sort(): void
    {
        sort($this->listArray);
    }

    public function sortDescending(): void
    {
        rsort($this->listArray);
    }

    /** @phpstan-param callable(TValue, TValue): (string|int|float) $callback */
    public function sortBy(callable $callback): void
    {
        $callback = Callback::curry($callback);

        usort(
            $this->listArray,
            fn (mixed $a, mixed $b): int => $callback($a) <=> $callback($b)
        );
    }

    /** @phpstan-param callable(TValue, TIndex=): (string|int|float) $callback */
    public function sortByDescending(callable $callback): void
    {
        $callback = Callback::curry($callback);

        usort(
            $this->listArray,
            fn (mixed $a, mixed $b): int => $callback($b) <=> $callback($a)
        );
    }

    /** Keeps only unique values inside the list. */
    public function unique(): void
    {
        $this->listArray = array_values(array_unique($this->listArray));
    }

    /**
     * Keeps only unique values by a given callback inside the list.
     *
     * @phpstan-template Unique
     *
     * @phpstan-param callable(TValue, TIndex=): Unique $callback
     */
    public function uniqueBy(callable $callback): void
    {
        $uniques = [];
        $list = [];
        $callback = Callback::curry($callback);

        foreach ($this as $i => $value) {
            $unique = $callback($value, $i);

            if (!in_array($unique, $uniques, true)) {
                $list[] = $value;
            }

            $uniques[] = $unique;
        }

        $this->listArray = $list;
    }

    /** Sort all items in a reverse order. */
    public function reverse(): void
    {
        $this->listArray = array_reverse($this->listArray, false);
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

    public function clear(): void
    {
        $this->listArray = [];
    }

    /** @phpstan-param IList<TValue> $list */
    public function append(IList $list): void
    {
        $this->listArray = [...$this, ...$list];
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

        return $counts
            ->pairs()
            ->asMutable();
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

    /** @phpstan-return \MF\Collection\Immutable\Generic\IList<TValue> */
    public function asImmutable(): \MF\Collection\Immutable\Generic\IList
    {
        /** @phpstan-var \MF\Collection\Immutable\Generic\IList<TValue> $immutableList */
        $immutableList = \MF\Collection\Immutable\Generic\ListCollection::from($this);

        return $immutableList;
    }

    /** @phpstan-return ISeq<TValue> */
    public function toSeq(): ISeq
    {
        /** @phpstan-var ISeq<TValue> $seq */
        $seq = Seq::from($this->listArray);

        return $seq;
    }
}
