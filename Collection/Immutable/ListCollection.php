<?php

namespace MF\Collection\Immutable;

use MF\Collection\ICollection;

class ListCollection implements IList
{
    /** @var array */
    protected $listArray;

    public function __construct()
    {
        $this->listArray = [];
    }

    public static function of(array $array, bool $recursive = false): IList
    {
        $list = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $list = $list->add(static::of($value, true));
            } else {
                $list = $list->add($value);
            }
        }

        return $list;
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this->listArray as $value) {
            if ($value instanceof ICollection) {
                $value = $value->toArray();
            }

            $array[] = $value;
        }

        return $array;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->listArray);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function add($value)
    {
        $list = clone $this;

        $list->listArray[] = $value;

        return $list;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function unshift($value)
    {
        $list = clone $this;

        array_unshift($list->listArray, $value);

        return $list;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return reset($this->listArray);
    }

    /**
     * @return mixed
     */
    public function last()
    {
        $list = $this->listArray;

        return array_pop($list);
    }

    /**
     * @return static
     */
    public function sort()
    {
        $sortedMap = $this->listArray;
        sort($sortedMap);

        return static::of($sortedMap);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->listArray);
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
     * @param mixed $value
     * @return int|false
     */
    protected function find($value)
    {
        return array_search($value, $this->listArray, true);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function removeFirst($value)
    {
        $index = $this->find($value);

        if ($index !== false) {
            return $this->removeIndex($index);
        }

        return $this;
    }

    /**
     * @param int $index
     * @return static
     */
    private function removeIndex($index)
    {
        $list = clone $this;

        unset($list->listArray[$index]);

        return static::of($list->listArray);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function removeAll($value)
    {
        return $this->filter(function ($item) use ($value) {
            return $item !== $value;
        });
    }

    /** @param callable $callback (value:mixed,index:int):void */
    public function each(callable $callback): void
    {
        foreach ($this->listArray as $i => $value) {
            $callback($value, $i);
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
     * @param callable $callback (value:mixed,index:int):mixed
     * @return static
     */
    public function map($callback)
    {
        $list = new static();

        return $this->mapList($list, $callback);
    }

    /**
     * @param IList $list
     * @param callable $callback
     * @return static
     */
    protected function mapList(IList $list, $callback)
    {
        $this->assertCallback($callback);

        foreach ($this->listArray as $i => $value) {
            $list = $list->add($callback($value, $i));
        }

        return $list;
    }

    /**
     * @param callable $callback (value:mixed,index:int):bool
     * @return static
     */
    public function filter($callback)
    {
        $list = new static();

        return $this->filterList($list, $callback);
    }

    /**
     * @param IList $list
     * @param callable $callback
     * @return static
     */
    protected function filterList(IList $list, $callback)
    {
        $this->assertCallback($callback);

        foreach ($this->listArray as $i => $value) {
            if ($callback($value, $i)) {
                $list = $list->add($value);
            }
        }

        return $list;
    }

    /**
     * @param callable $reducer (total:mixed,value:mixed,index:int,list:List):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $this->assertCallback($reducer);

        $total = $initialValue;

        foreach ($this->listArray as $i => $value) {
            $total = $reducer($total, $value, $i, $this);
        }

        return $total;
    }

    /**
     * @return \MF\Collection\Mutable\ListCollection
     */
    public function asMutable()
    {
        return \MF\Collection\Mutable\ListCollection::of($this->toArray());
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
        return empty($this->listArray);
    }
}
