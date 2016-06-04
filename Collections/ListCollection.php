<?php

namespace MFCollections\Collections;

class ListCollection implements ListInterface
{
    /** @var array */
    private $listArray;

    public function __construct()
    {
        $this->listArray = [];
    }

    /**
     * @param array $array
     * @param bool $recursive
     * @return ListCollection
     */
    public static function createFromArray(array $array, $recursive = false)
    {
        $list = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $list->add(static::createFromArray($value, true));
            } else {
                $list->add($value);
            }
        }

        return $list;
    }

    /** @return array */
    public function toArray()
    {
        $array = [];

        foreach ($this->listArray as $value) {
            if ($value instanceof CollectionInterface) {
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
     */
    public function add($value)
    {
        $this->listArray[] = $value;
    }

    /**
     * @param mixed $value
     */
    public function unshift($value)
    {
        array_unshift($this->listArray, $value);
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->listArray);
    }

    /**
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->listArray);
    }

    /**
     * @return mixed
     */
    public function first()
    {
        $list = $this->listArray;

        return array_shift($list);
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

        return static::createFromArray($sortedMap);
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
    public function contains($value)
    {
        return $this->find($value) !== false;
    }

    /**
     * @param mixed $value
     * @return int|false
     */
    private function find($value)
    {
        return array_search($value, $this->listArray, true);
    }

    /**
     * @param mixed $value
     */
    public function removeFirst($value)
    {
        $index = $this->find($value);

        if ($index !== false) {
            $this->removeIndex($index);
        }
    }

    /**
     * @param int $index
     * @param bool $normalize
     */
    private function removeIndex($index, $normalize = true)
    {
        unset($this->listArray[$index]);

        if ($normalize) {
            $this->normalize();
        }
    }

    private function normalize()
    {
        $list = $this->listArray;
        $this->listArray = [];

        foreach ($list as $value) {
            $this->listArray[] = $value;
        }
    }

    /**
     * @param mixed $value
     */
    public function removeAll($value)
    {
        $list = $this->listArray;
        $this->listArray = [];

        foreach ($list as $key => $val) {
            if ($value !== $val) {
                $this->listArray[] = $val;
            }
        }
    }

    /** @param callable(value:mixed,index:int):void $callback */
    public function each($callback)
    {
        $this->assertCallback($callback);

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
     * @param callable(value:mixed,index:int):mixed $callback
     * @return static
     */
    public function map($callback)
    {
        $list = new static();

        return $this->mapList($list, $callback);
    }

    /**
     * @param ListInterface $list
     * @param callable $callback
     * @return ListInterface
     */
    protected function mapList(ListInterface $list, $callback)
    {
        $this->assertCallback($callback);

        foreach ($this->listArray as $i => $value) {
            $list->add($callback($value, $i));
        }

        return $list;
    }

    /**
     * @param callable(value:mixed,index:int):bool $callback
     * @return static
     */
    public function filter($callback)
    {
        $list = new static();

        return $this->filterList($list, $callback);
    }

    /**
     * @param ListInterface $list
     * @param callable $callback
     * @return ListInterface
     */
    protected function filterList(ListInterface $list, $callback)
    {
        $this->assertCallback($callback);

        foreach ($this->listArray as $i => $value) {
            if ($callback($value, $i)) {
                $list->add($value);
            }
        }

        return $list;
    }
}
