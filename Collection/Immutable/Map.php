<?php

namespace MF\Collection\Immutable;

class Map implements IMap
{
    /** @var array */
    protected $mapArray;

    /** @var array< Tuple<string, callable> > */
    protected $modifiers;

    /**
     * @param array $array
     * @param bool $recursive
     * @return static
     */
    public static function from(array $array, bool $recursive = false)
    {
        $map = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $map = $map->set($key, static::from($value, true));
            } else {
                $map = $map->set($key, $value);
            }
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
            foreach ($this->modifiers as $item) {
                list($type, $callback) = $item;

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

    public function getIterator(): \Generator
    {
        $this->applyModifiers();

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
        $this->applyModifiers();

        return $this->containsKey($offset);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function containsKey($key): bool
    {
        $this->applyModifiers();

        return array_key_exists($key, $this->mapArray);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value): bool
    {
        $this->applyModifiers();

        return $this->find($value) !== false;
    }

    /**
     * @param $value
     * @return mixed|false
     */
    public function find($value)
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
        $this->applyModifiers();

        return $this->get($offset);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        $this->applyModifiers();

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
        $this->applyModifiers();
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
        $this->applyModifiers();
        $map = clone $this;
        unset($map->mapArray[$key]);

        return $map;
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
    public function map($callback)
    {
        $this->assertCallback($callback);

        $map = clone $this;
        $map->modifiers[] = [self::MAP, $callback];

        return $map;
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):bool
     * @return static
     */
    public function filter($callback)
    {
        $this->assertCallback($callback);

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
        $this->applyModifiers();

        return empty($this->mapArray);
    }

    /** @return \MF\Collection\Mutable\IMap */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Map::from($this->toArray());
    }
}
