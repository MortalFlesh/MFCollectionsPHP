<?php

namespace MF\Collection\Immutable;

class Map implements MapInterface
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
    public static function createFromArray(array $array, $recursive = false)
    {
        $map = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $map = $map->set($key, static::createFromArray($value, true));
            } else {
                $map = $map->set($key, $value);
            }
        }

        return $map;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->mapArray);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function containsKey($key)
    {
        return array_key_exists($key, $this->mapArray);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value)
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
        throw new \BadMethodCallException('Immutable map cannot be used as array to set value. Use set() method instead.');
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
        throw new \BadMethodCallException('Immutable map cannot be used as array to unset value. Use remove() method instead.');
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

    /**
     * @return int
     */
    public function count()
    {
        return count($this->mapArray);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];

        foreach ($this->mapArray as $key => $value) {
            if ($value instanceof CollectionInterface) {
                $value = $value->toArray();
            }

            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * @param callable (value:mixed,index:mixed):void $callback
     */
    public function each($callback)
    {
        $this->assertCallback($callback);

        foreach ($this->mapArray as $key => $value) {
            $callback($value, $key);
        }
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
     * @param callable (key:mixed,value:mixed):mixed $callback
     * @return static
     */
    public function map($callback)
    {
        $map = new static();

        return $this->mapToMap($map, $callback);
    }

    /**
     * @param MapInterface $map
     * @param callable $callback
     * @return MapInterface
     */
    protected function mapToMap(MapInterface $map, $callback)
    {
        $this->assertCallback($callback);

        foreach ($this->mapArray as $key => $value) {
            $map = $map->set($key, $callback($key, $value));
        }

        return $map;
    }

    /**
     * @param callable (key:mixed,value:mixed):bool $callback
     * @return static
     */
    public function filter($callback)
    {
        $map = new static();

        return $this->filterToMap($map, $callback);
    }

    /**
     * @param MapInterface $map
     * @param callable $callback
     * @return MapInterface
     */
    protected function filterToMap(MapInterface $map, $callback)
    {
        $this->assertCallback($callback);

        foreach ($this->mapArray as $key => $value) {
            if ($callback($key, $value)) {
                $map = $map->set($key, $value);
            }
        }

        return $map;
    }

    /**
     * @return ListCollection
     */
    public function keys()
    {
        return ListCollection::createFromArray(array_keys($this->mapArray));
    }

    /**
     * @return ListCollection
     */
    public function values()
    {
        return ListCollection::createFromArray(array_values($this->mapArray));
    }

    /**
     * @param callable (total:mixed,value:mixed,key:mixed,map:Map):mixed $reducer
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $this->assertCallback($reducer);

        $total = $initialValue;

        foreach ($this->mapArray as $key => $value) {
            $total = $reducer($total, $value, $key, $this);
        }

        return $total;
    }

    /** @return \MF\Collection\MapInterface */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Map::createFromArray($this->toArray());
    }

    /**
     * @return static
     */
    public function clear()
    {
        return new static();
    }

    /** @return bool */
    public function isEmpty()
    {
        return empty($this->mapArray);
    }
}
