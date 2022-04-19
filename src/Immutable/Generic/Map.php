<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Assertion;
use MF\Collection\Exception\BadMethodCallException;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Helper\Callback;
use MF\Collection\Helper\Collection;
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
    public static function from(iterable $source): IMap
    {
        $mapArray = [];
        foreach ($source as $key => $value) {
            $mapArray[$key] = $value;
        }

        return new static($mapArray);
    }

    public static function create(iterable $source, callable $creator): IMap
    {
        $mapArray = [];
        $creator = Callback::curry($creator);

        foreach ($source as $key => $value) {
            $mapArray[$key] = $creator($value, $key);
        }

        return new static($mapArray);
    }

    /**
     * @phpstan-param iterable<ITuple|KVPair<TKey, TValue>|array{0: TKey, 1: TValue}> $pairs
     * @phpstan-return IMap<TKey, TValue>
     */
    public static function fromPairs(iterable $pairs): IMap
    {
        $mapArray = [];

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
            $mapArray[$key] = $value;
        }

        return new static($mapArray);
    }

    /** @phpstan-param array<TKey, TValue> $mapArray */
    public function __construct(private readonly array $mapArray = [])
    {
    }

    public function toArray(): array
    {
        return Collection::immutableToArray($this);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->mapArray;
    }

    public function offsetExists(mixed $offset): bool
    {
        Assertion::isKey($offset);

        return $this->containsKey($offset);
    }

    public function containsKey(int|string $key): bool
    {
        return array_key_exists($key, $this->mapArray);
    }

    public function contains(mixed $value): bool
    {
        return in_array($value, $this->mapArray, true);
    }

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

    public function find(int|string $key): mixed
    {
        return $this->mapArray[$key] ?? null;
    }

    public function offsetGet(mixed $offset): mixed
    {
        Assertion::isKey($offset);

        return $this->get($offset);
    }

    public function get(int|string $key): mixed
    {
        Assertion::keyExists($this->mapArray, $key);

        return $this->mapArray[$key];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException(
            'Immutable map cannot be used as array to set value. Use set() method instead.',
        );
    }

    public function set(int|string $key, mixed $value): IMap
    {
        $mapArray = $this->mapArray;
        $mapArray[$key] = $value;

        return new static($mapArray);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException(
            'Immutable map cannot be used as array to unset value. Use remove() method instead.',
        );
    }

    public function remove(int|string $key): IMap
    {
        Assertion::keyExists($this->mapArray, $key);

        $mapArray = $this->mapArray;
        unset($mapArray[$key]);

        return new static($mapArray);
    }

    public function count(): int
    {
        return count($this->mapArray);
    }

    public function each(callable $callback): void
    {
        $callback = Callback::curry($callback);

        foreach ($this as $key => $value) {
            $callback($value, $key);
        }
    }

    public function map(callable $callback): IMap
    {
        $map = [];
        $callback = Callback::curry($callback);

        foreach ($this as $key => $v) {
            $map[$key] = $callback($v, $key);
        }

        /** @phpstan-var array<TKey, TValue> $map */
        return static::from($map);
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):bool
     */
    public function filter(callable $callback): IMap
    {
        $map = [];
        $callback = Callback::curry($callback);

        foreach ($this as $key => $v) {
            if ($callback($v, $key) === true) {
                $map[$key] = $v;
            }
        }

        return static::from($map);
    }

    public function keys(): IList
    {
        /** @phpstan-var IList<TKey> $keys */
        $keys = ListCollection::from(array_keys($this->mapArray));

        return $keys;
    }

    public function values(): IList
    {
        /** @phpstan-var IList<TValue> $values */
        $values = ListCollection::from($this->mapArray);

        return $values;
    }

    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $reducer = Callback::curry($reducer);
        $state = $initialValue;

        foreach ($this as $key => $value) {
            $state = $reducer($state, $value, $key, $this);
        }

        return $state;
    }

    public function clear(): IMap
    {
        return new static();
    }

    public function isEmpty(): bool
    {
        return empty($this->mapArray);
    }

    public function asMutable(): \MF\Collection\Mutable\Generic\IMap
    {
        /** @phpstan-var \MF\Collection\Mutable\Generic\IMap<TKey, TValue> $map */
        $map = \MF\Collection\Mutable\Generic\Map::from($this);

        return $map;
    }

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

    public function findKey(mixed $value): mixed
    {
        return array_search($value, $this->mapArray, true) ?: null;
    }

    public function pairs(): IList
    {
        /** @phpstan-var IList<KVPair<TKey, TValue>> $pairs */
        $pairs = ListCollection::create($this, fn ($value, $key) => new KVPair($key, $value));

        return $pairs;
    }

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
}
