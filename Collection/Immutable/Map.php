<?php

namespace MF\Collection\Immutable;

class Map implements IMap
{
    /** @var array */
    protected $mapArray;

    public function __construct()
    {
        $this->mapArray = [];
    }

    /**
     * @param array $array
     * @param bool $recursive
     * @return static
     */
    public static function of(array $array, bool $recursive = false)
    {
        $map = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $map = $map->set($key, static::of($value, true));
            } else {
                $map = $map->set($key, $value);
            }
        }

        return $map;
    }

    public function getIterator(): \Generator
    {
        foreach ($this->mapArray as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->containsKey($offset);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function containsKey($key): bool
    {
        return array_key_exists($key, $this->mapArray);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value): bool
    {
        return $this->find($value) !== false;
    }

    /**
     * @param $value
     * @return mixed|false
     */
    public function find($value)
    {
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

    /**
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->mapArray[$key];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException(
            'Immutable map cannot be used as array to set value. Use set() method instead.'
        );
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return static
     */
    public function set($key, $value)
    {
        if (is_object($key)) {
            throw new \InvalidArgumentException('Key cannot be an Object');
        }
        if (is_array($key)) {
            throw new \InvalidArgumentException('Key cannot be an Array');
        }

        $map = clone $this;
        $map->mapArray[$key] = $value;

        return $map;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException(
            'Immutable map cannot be used as array to unset value. Use remove() method instead.'
        );
    }

    /**
     * @param mixed $key
     * @return static
     */
    public function remove($key)
    {
        $map = clone $this;
        unset($map->mapArray[$key]);

        return $map;
    }

    public function count(): int
    {
        return count($this->mapArray);
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this as $key => $value) {
            if ($value instanceof ICollection) {
                $value = $value->toArray();
            }

            $array[$key] = $value;
        }

        return $array;
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
    public function map($callback)
    {
        $map = new static();

        return $this->mapToMap($map, $callback);
    }

    protected function mapToMap(IMap $map, callable $callback)
    {
        foreach ($this as $key => $value) {
            $map = $map->set($key, $callback($key, $value));
        }

        return $map;
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):bool
     * @return static
     */
    public function filter($callback)
    {
        $map = new static();

        return $this->filterToMap($map, $callback);
    }

    protected function filterToMap(IMap $map, callable $callback)
    {
        foreach ($this as $key => $value) {
            if ($callback($key, $value)) {
                $map = $map->set($key, $value);
            }
        }

        return $map;
    }

    /**
     * @return IList
     */
    public function keys()
    {
        return ListCollection::of(array_keys($this->mapArray));
    }

    /**
     * @return IList
     */
    public function values()
    {
        return ListCollection::of(array_values($this->mapArray));
    }

    /**
     * @param callable $reducer (total:mixed,value:mixed,key:mixed,map:Map):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $this->assertCallback($reducer);

        $total = $initialValue;

        foreach ($this as $key => $value) {
            $total = $reducer($total, $value, $key, $this);
        }

        return $total;
    }

    /**
     * @param callable $callback
     */
    private function assertCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback must be callable');
        }
    }

    /**
     * @return static
     */
    public function clear()
    {
        return new static();
    }

    public function isEmpty(): bool
    {
        return empty($this->mapArray);
    }

    /** @return \MF\Collection\Mutable\IMap */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Map::of($this->toArray());
    }
}
