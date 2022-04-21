<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Assertion;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Helper\Callback;
use MF\Collection\Helper\Collection;
use MF\Collection\Immutable\Generic\ISeq;
use MF\Collection\Immutable\Generic\KVPair;
use MF\Collection\Immutable\Generic\Seq;
use MF\Collection\Immutable\ITuple;
use MF\Collection\Immutable\Tuple;

/**
 * @phpstan-template TKey of int|string
 * @phpstan-template TValue
 *
 * @phpstan-implements IMap<TKey, TValue>
 */
class Map implements IMap
{
    /**
     * @phpstan-param iterable<TKey, TValue> $source
     * @phpstan-return IMap<TKey, TValue>
     */
    public static function from(iterable $source): IMap
    {
        $map = new static();

        foreach ($source as $key => $value) {
            $map->mapArray[$key] = $value;
        }

        return $map;
    }

    /**
     * @phpstan-param iterable<ITuple|KVPair<TKey, TValue>|array{0: TKey, 1: TValue}> $pairs
     * @phpstan-return IMap<TKey, TValue>
     *
     * @throws InvalidArgumentException
     */
    public static function fromPairs(iterable $pairs): IMap
    {
        $map = new static();

        foreach ($pairs as $pair) {
            if ($pair instanceof KVPair) {
                $key = $pair->getKey();
                $value = $pair->getValue();
            } elseif ($pair instanceof ITuple) {
                [$key, $value] = $pair;
            } elseif (is_array($pair)) {
                [$key, $value] = $pair;
            } else {
                throw new InvalidArgumentException('Value is not a pair');
            }

            /**
             * @phpstan-var TKey $key
             * @phpstan-var TValue $value
             */
            $map->mapArray[$key] = $value;
        }

        return $map;
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param iterable<TKey, T> $source
     * @phpstan-param callable(T, TKey): TValue $creator
     * @phpstan-return IMap<TKey, TValue>
     */
    public static function create(iterable $source, callable $creator): IMap
    {
        $map = new static();
        $creator = Callback::curry($creator);

        foreach ($source as $key => $value) {
            $map->mapArray[$key] = $creator($value, $key);
        }

        return $map;
    }

    /** @phpstan-param array<TKey, TValue> $mapArray */
    public function __construct(private array $mapArray = [])
    {
    }

    public function count(): int
    {
        return count($this->mapArray);
    }

    /** @phpstan-return \Traversable<TKey, TValue> */
    public function getIterator(): \Traversable
    {
        yield from $this->mapArray;
    }

    public function isEmpty(): bool
    {
        return empty($this->mapArray);
    }

    /** @phpstan-param TValue $value */
    public function contains(mixed $value): bool
    {
        return in_array($value, $this->mapArray, true);
    }

    /** @phpstan-param callable(TValue, TKey=): bool $callback */
    public function containsBy(callable $callback): bool
    {
        $callback = Callback::curry($callback);

        foreach ($this as $k => $v) {
            if ($callback($v, $k) === true) {
                return true;
            }
        }

        return false;
    }

    /** @phpstan-return array<TKey, TValue> */
    public function toArray(): array
    {
        return Collection::mutableToArray($this);
    }

    /** @param callable(TValue, TKey=): void $callback */
    public function each(callable $callback): void
    {
        $callback = Callback::curry($callback);

        foreach ($this as $key => $value) {
            $callback($value, $key);
        }
    }

    /**
     * Tests if all elements of the collection satisfy the given predicate.
     *
     * @phpstan-param callable(TValue, TKey=): bool $predicate
     */
    public function forAll(callable $predicate): bool
    {
        $predicate = Callback::curry($predicate);

        foreach ($this as $key => $v) {
            if ($predicate($v, $key) !== true) {
                return false;
            }
        }

        return true;
    }

    public function implode(string $glue): string
    {
        return implode($glue, $this->mapArray);
    }

    /** @phpstan-param TKey $key */
    public function containsKey(int|string $key): bool
    {
        return array_key_exists($key, $this->mapArray);
    }

    /**
     * @phpstan-param TKey $key
     * @phpstan-return TValue|null
     */
    public function find(int|string $key): mixed
    {
        return $this->mapArray[$key] ?? null;
    }

    /**
     * @phpstan-param TValue $value
     * @phpstan-return TKey|null
     */
    public function findKey(mixed $value): mixed
    {
        return array_search($value, $this->mapArray, true) ?: null;
    }

    /**
     * @phpstan-return TValue
     *
     * @throws InvalidArgumentException
     */
    public function get(int|string $key): mixed
    {
        Assertion::keyExists($this->mapArray, $key);

        return $this->mapArray[$key];
    }

    /**
     * @phpstan-param TKey $key
     * @phpstan-param TValue $value
     */
    public function set(int|string $key, mixed $value): void
    {
        $this->mapArray[$key] = $value;
    }

    /**
     * @phpstan-param TKey $key
     *
     * @throws InvalidArgumentException
     */
    public function remove(int|string $key): void
    {
        Assertion::keyExists($this->mapArray, $key);

        unset($this->mapArray[$key]);
    }

    public function clear(): void
    {
        $this->mapArray = [];
    }

    /** @phpstan-return IList<TKey> */
    public function keys(): IList
    {
        /** @phpstan-var IList<TKey> $list */
        $list = ListCollection::from(array_keys($this->mapArray));

        return $list;
    }

    /** @phpstan-return IList<TValue> */
    public function values(): IList
    {
        /** @phpstan-var IList<TValue> $values */
        $values = ListCollection::from($this->mapArray);

        return $values;
    }

    /** @phpstan-return IList<KVPair<TKey, TValue>> */
    public function pairs(): IList
    {
        /** @phpstan-var IList<KVPair<TKey, TValue>> $pairs */
        $pairs = ListCollection::create($this, fn ($value, $key) => new KVPair($key, $value));

        return $pairs;
    }

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TKey): T $callback
     */
    public function map(callable $callback): void
    {
        $callback = Callback::curry($callback);
        $map = [];

        foreach ($this as $key => $v) {
            $map[$key] = $callback($v, $key);
        }

        /** @phpstan-var array<TKey, TValue> $map */
        $this->mapArray = $map;
    }

    /** @phpstan-param callable(TValue, TKey): bool $callback */
    public function filter(callable $callback): void
    {
        $map = [];
        $callback = Callback::curry($callback);

        foreach ($this as $key => $v) {
            if ($callback($v, $key) === true) {
                $map[$key] = $v;
            }
        }

        $this->mapArray = $map;
    }

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State, TValue, TKey=, IMap<TKey, TValue>=): State $callback
     * @phpstan-param State $initialValue
     * @phpstan-return State
     */
    public function reduce(callable $callback, mixed $initialValue = null): mixed
    {
        $callback = Callback::curry($callback);
        $state = $initialValue;

        foreach ($this as $key => $value) {
            $state = $callback($state, $value, $key, $this);
        }

        return $state;
    }

    /** @phpstan-return \MF\Collection\Immutable\Generic\IMap<TKey, TValue> */
    public function asImmutable(): \MF\Collection\Immutable\Generic\IMap
    {
        /** @phpstan-var \MF\Collection\Immutable\Generic\IMap<TKey, TValue> $map */
        $map = \MF\Collection\Immutable\Generic\Map::from($this);

        return $map;
    }

    /** @phpstan-return IList<ITuple> */
    public function toList(): IList
    {
        /** @phpstan-var IList<ITuple> $list */
        $list = ListCollection::create($this, fn ($value, $key) => Tuple::of($key, $value));

        return $list;
    }

    /** @phpstan-return ISeq<ITuple> */
    public function toSeq(): ISeq
    {
        $map = clone $this;

        return Seq::init(function () use ($map) {
            foreach ($map as $key => $value) {
                yield Tuple::of($key, $value);
            }
        });
    }

    /**
     * @see IMap::containsKey()
     *
     * @phpstan-param TKey $offset
     *
     * @throws InvalidArgumentException
     */
    public function offsetExists(mixed $offset): bool
    {
        Assertion::isKey($offset);

        return $this->containsKey($offset);
    }

    /**
     * @see IMap::get()
     *
     * @phpstan-param TKey $offset
     * @phpstan-return TValue
     *
     * @throws InvalidArgumentException
     */
    public function offsetGet(mixed $offset): mixed
    {
        Assertion::isKey($offset);

        return $this->get($offset);
    }

    /**
     * @see IMap::set()
     *
     * @phpstan-param TKey $offset
     * @phpstan-param TValue $value
     *
     * @throws InvalidArgumentException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        Assertion::isKey($offset);

        $this->set($offset, $value);
    }

    /**
     * @see IMap::remove()
     *
     * @phpstan-param TKey $offset
     *
     * @throws InvalidArgumentException
     */
    public function offsetUnset(mixed $offset): void
    {
        Assertion::isKey($offset);

        $this->remove($offset);
    }
}
