<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

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
    /** @phpstan-var array<TKey, TValue> */
    protected array $mapArray;

    public static function from(iterable $source): IMap
    {
        $map = new static();

        foreach ($source as $key => $value) {
            $map->mapArray[$key] = $value;
        }

        return $map;
    }

    public static function create(iterable $source, callable $creator): IMap
    {
        $map = new static();
        $creator = Callback::curry($creator);

        foreach ($source as $key => $value) {
            $map->mapArray[$key] = $creator($value, $key);
        }

        return $map;
    }

    /**
     * @phpstan-param iterable<ITuple|KVPair<TKey, TValue>|array{0: TKey, 1: TValue}> $pairs
     * @phpstan-return IMap<TKey, TValue>
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
                throw new \InvalidArgumentException('Value is not a pair');
            }

            /**
             * @phpstan-var TKey $key
             * @phpstan-var TValue $value
             */
            $map->mapArray[$key] = $value;
        }

        return $map;
    }

    public function __construct()
    {
        $this->mapArray = [];
    }

    public function toArray(): array
    {
        return Collection::mutableToArray($this);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->mapArray;
    }

    public function offsetExists(mixed $offset): bool
    {
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
        return $this->get($offset);
    }

    public function get(int|string $key): mixed
    {
        if (!array_key_exists($key, $this->mapArray)) {
            $this->undefinedKey($key);
        }

        return $this->mapArray[$key];
    }

    private function undefinedKey(int|string $key): \Throwable
    {
        return throw new \InvalidArgumentException(sprintf('Key %s is not defined.', $key));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function set(int|string $key, mixed $value): void
    {
        $this->mapArray[$key] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    public function remove(int|string $key): void
    {
        unset($this->mapArray[$key]);
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

    public function map(callable $callback): void
    {
        $callback = Callback::curry($callback);
        $map = [];

        foreach ($this as $key => $v) {
            $map[$key] = $callback($v, $key);
        }

        $this->mapArray = $map;
    }

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

    public function keys(): IList
    {
        return ListCollection::from(array_keys($this->mapArray));
    }

    public function values(): IList
    {
        return ListCollection::from(array_values($this->mapArray));
    }

    public function reduce(callable $callback, mixed $initialValue = null): mixed
    {
        $callback = Callback::curry($callback);
        $state = $initialValue;

        foreach ($this as $key => $value) {
            $state = $callback($state, $value, $key, $this);
        }

        return $state;
    }

    public function clear(): void
    {
        $this->mapArray = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->mapArray);
    }

    public function asImmutable(): \MF\Collection\Immutable\Generic\IMap
    {
        /** @phpstan-var \MF\Collection\Immutable\Generic\IMap<TKey, TValue> $map */
        $map = \MF\Collection\Immutable\Generic\Map::from($this);

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
        return ListCollection::create($this, fn ($value, $key) => new KVPair($key, $value));
    }

    public function toList(): IList
    {
        return ListCollection::create($this, fn ($value, $key) => Tuple::of($key, $value));
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
