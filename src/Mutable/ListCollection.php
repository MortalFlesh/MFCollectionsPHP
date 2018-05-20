<?php declare(strict_types=1);

namespace MF\Collection\Mutable;

class ListCollection implements IList
{
    /** @var array */
    protected $listArray;

    /** @var array< Tuple<string, callable> > */
    protected $modifiers;

    public static function of(...$values)
    {
        return static::from($values);
    }

    public static function from(array $array, bool $recursive = false)
    {
        $list = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $list->add(static::from($value, true));
            } else {
                $list->add($value);
            }
        }

        return $list;
    }

    public static function create(iterable $source, $creator)
    {
        $list = new static();

        foreach ($source as $index => $value) {
            $list->add($creator($value, $index));
        }

        return $list;
    }

    public function __construct()
    {
        $this->listArray = [];
        $this->modifiers = [];
    }

    public function toArray(): array
    {
        $this->modifiers[] = [
            self::MAP,
            function ($value) {
                return $value instanceof ICollection
                    ? $value->toArray()
                    : $value;
            },
        ];

        $this->applyModifiers();

        return $this->listArray;
    }

    protected function applyModifiers(): void
    {
        if (empty($this->modifiers) || empty($this->listArray)) {
            return;
        }

        $listArray = [];
        foreach ($this->listArray as $i => $value) {
            foreach ($this->modifiers as $item) {
                [$type, $callback] = $item;

                if ($type === self::MAP) {
                    $value = $callback($value, $i);
                } elseif ($type === self::FILTER && !$callback($value, $i)) {
                    continue 2;
                }
            }

            $listArray[] = $value;
        }

        $this->listArray = $listArray;
        $this->modifiers = [];
    }

    public function getIterator(): \Generator
    {
        // todo try to optimize - there are 2 loops for iterating and applying modifiers
        $this->applyModifiers();

        foreach ($this->listArray as $i => $value) {
            yield $i => $value;
        }
    }

    /**
     * @param mixed $value
     */
    public function add($value): void
    {
        $this->applyModifiers();
        $this->listArray[] = $value;
    }

    /**
     * @param mixed $value
     */
    public function unshift($value): void
    {
        $this->applyModifiers();
        array_unshift($this->listArray, $value);
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        $this->applyModifiers();

        return array_pop($this->listArray);
    }

    /**
     * @return mixed
     */
    public function shift()
    {
        $this->applyModifiers();

        return array_shift($this->listArray);
    }

    /**
     * @return mixed
     */
    public function first()
    {
        $this->applyModifiers();

        return reset($this->listArray);
    }

    /**
     * @return mixed
     */
    public function last()
    {
        $this->applyModifiers();
        $list = $this->listArray;

        return array_pop($list);
    }

    public function sort()
    {
        $this->applyModifiers();
        $sortedMap = $this->listArray;
        sort($sortedMap);

        return static::from($sortedMap);
    }

    public function count(): int
    {
        $this->applyModifiers();

        return count($this->listArray);
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
     * @param mixed $value
     * @return int|false
     */
    private function find($value)
    {
        $this->applyModifiers();

        return array_search($value, $this->listArray, true);
    }

    /**
     * @param mixed $value
     */
    public function removeFirst($value): void
    {
        $this->applyModifiers();
        $index = $this->find($value);

        if ($index !== false) {
            $this->removeIndex($index);
        }
    }

    private function removeIndex(int $index, bool $normalize = true): void
    {
        unset($this->listArray[$index]);

        if ($normalize) {
            $this->normalize();
        }
    }

    private function normalize(): void
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
    public function removeAll($value): void
    {
        $this->modifiers[] = [
            self::FILTER,
            function ($val) use ($value) {
                return $value !== $val;
            },
        ];
        $this->applyModifiers();
    }

    /** @param callable $callback (value:mixed,index:int):void */
    public function each(callable $callback): void
    {
        foreach ($this as $i => $value) {
            $callback($value, $i);
        }
    }

    /**
     * @param callable $callback
     */
    protected function assertCallback($callback): void
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
        $this->assertCallback($callback);

        $list = clone $this;
        $list->modifiers[] = [self::MAP, $callback];

        return $list;
    }

    /**
     * @param callable $callback (value:mixed,index:int):bool
     * @return static
     */
    public function filter($callback)
    {
        $this->assertCallback($callback);

        $list = clone $this;
        $list->modifiers[] = [self::FILTER, $callback];

        return $list;
    }

    /**
     * @param callable $reducer (total:mixed,value:mixed,index:int,list:IList):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $this->assertCallback($reducer);

        $total = $initialValue;

        foreach ($this as $i => $value) {
            $total = $reducer($total, $value, $i, $this);
        }

        return $total;
    }

    public function clear(): void
    {
        $this->listArray = [];
        $this->modifiers = [];
    }

    public function isEmpty(): bool
    {
        $this->applyModifiers();

        return empty($this->listArray);
    }

    /** @return \MF\Collection\Immutable\IList */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\ListCollection::from($this->toArray());
    }

    public function implode(string $glue): string
    {
        return implode($glue, $this->listArray);
    }
}
