<?php

namespace MF\Collection\Mutable;

class ListCollection implements IList
{
    /** @var array */
    private $listArray;

    public function __construct()
    {
        $this->listArray = [];
    }

    public static function of(array $array, bool $recursive = false): IList
    {
        $list = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $list->add(static::of($value, true));
            } else {
                $list->add($value);
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

    public function getIterator(): \Generator
    {
        foreach ($this->listArray as $index => $value) {
            yield $index => $value;
        }
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
     * @return IList
     */
    protected function mapList(IList $list, $callback)
    {
        $this->assertCallback($callback);

        foreach ($this->listArray as $i => $value) {
            $list->add($callback($value, $i));
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
                $list->add($value);
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

    /** @return \MF\Collection\Immutable\IList */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\ListCollection::of($this->toArray());
    }

    public function clear()
    {
        $this->listArray = [];
    }

    public function isEmpty(): bool
    {
        return empty($this->listArray);
    }
}
