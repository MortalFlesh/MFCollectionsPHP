<?php declare(strict_types=1);

namespace MF\Collection\Mutable;

use MF\Collection\Assertion;

class Map implements IMap
{
    protected array $mapArray;
    /** @var array of type <string, callable> */
    protected array $modifiers;

    /**
     * @return static
     */
    public static function from(array $array, bool $recursive = false)
    {
        $map = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $map->set($key, static::from($value, true));
            } else {
                $map->set($key, $value);
            }
        }

        return $map;
    }

    public static function create(iterable $source, callable $creator)
    {
        $map = new static();

        foreach ($source as $key => $value) {
            $map->set($key, $creator($value, $key));
        }

        return $map;
    }

    public function __construct()
    {
        $this->mapArray = [];
        $this->modifiers = [];
    }

    public function toArray(): array
    {
        $this->modifiers[] = [
            self::MAP,
            function ($key, $value) {
                return $value instanceof ICollection
                    ? $value->toArray()
                    : $value;
            },
        ];

        $this->applyModifiers();

        return $this->mapArray;
    }

    protected function applyModifiers(): void
    {
        if (empty($this->modifiers) || empty($this->mapArray)) {
            return;
        }

        $mapArray = [];
        foreach ($this->mapArray as $key => $value) {
            foreach ($this->modifiers as [$type, $callback]) {
                if ($type === self::MAP) {
                    $value = $callback($key, $value);
                } elseif ($type === self::FILTER && !$callback($key, $value)) {
                    continue 2;
                }
            }

            $mapArray[$key] = $value;
        }

        $this->mapArray = $mapArray;
        $this->modifiers = [];
    }

    public function getIterator(): iterable
    {
        $this->applyModifiers();

        yield from $this->mapArray;
    }

    /** @param mixed $offset */
    public function offsetExists($offset): bool
    {
        $this->applyModifiers();

        return $this->containsKey($offset);
    }

    public function containsKey(mixed $key): bool
    {
        $this->applyModifiers();

        return array_key_exists($key, $this->mapArray);
    }

    public function contains(mixed $value): bool
    {
        return $this->find($value) !== false;
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):bool
     */
    public function containsBy(callable $callback): bool
    {
        foreach ($this as $k => $v) {
            if ($callback($k, $v) === true) {
                return true;
            }
        }

        return false;
    }

    public function find(mixed $value): mixed
    {
        $this->applyModifiers();

        return array_search($value, $this->mapArray, true);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function get(mixed $key): mixed
    {
        $this->applyModifiers();

        return $this->mapArray[$key];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function set(mixed $key, mixed $value): void
    {
        Assertion::isValidKey($key);

        $this->applyModifiers();

        $this->mapArray[$key] = $value;
    }

    /** @param mixed $offset */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    public function remove(mixed $key): void
    {
        $this->applyModifiers();

        unset($this->mapArray[$key]);
    }

    public function count(): int
    {
        $this->applyModifiers();

        return count($this->mapArray);
    }

    /**
     * @param callable $callback (value:mixed,index:mixed):void
     */
    public function each(callable $callback): void
    {
        foreach ($this as $key => $value) {
            $callback($value, $key);
        }
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):mixed
     * @return static
     */
    public function map(callable $callback)
    {
        $map = clone $this;
        $map->modifiers[] = [self::MAP, $callback];

        return $map;
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):bool
     * @return static
     */
    public function filter(callable $callback)
    {
        $map = clone $this;
        $map->modifiers[] = [self::FILTER, $callback];

        return $map;
    }

    /**
     * @return IList
     */
    public function keys()
    {
        $this->applyModifiers();

        return ListCollection::from(array_keys($this->mapArray));
    }

    /**
     * @return IList
     */
    public function values()
    {
        $this->applyModifiers();

        return ListCollection::from(array_values($this->mapArray));
    }

    /**
     * @param callable $reducer (value:mixed,index:int):mixed
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $total = $initialValue;

        foreach ($this as $key => $value) {
            $total = $reducer($total, $value, $key, $this);
        }

        return $total;
    }

    public function clear(): void
    {
        $this->mapArray = [];
        $this->modifiers = [];
    }

    public function isEmpty(): bool
    {
        $this->applyModifiers();

        return empty($this->mapArray);
    }

    /**
     * @return \MF\Collection\Immutable\IMap
     */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\Map::from($this->toArray());
    }
}
