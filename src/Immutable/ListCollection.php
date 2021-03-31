<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use MF\Collection\Exception\LogicException;
use MF\Collection\ICollection;

class ListCollection implements IList
{
    protected array $listArray;
    /** @var array of type <string, callable> */
    protected array $modifiers;

    public static function of(mixed ...$values)
    {
        return static::from($values);
    }

    public static function from(array $array, bool $recursive = false)
    {
        $list = new static();

        foreach ($array as $key => $value) {
            if ($recursive && is_array($value)) {
                $list = $list->add(static::from($value, true));
            } else {
                $list = $list->add($value);
            }
        }

        return $list;
    }

    public static function create(iterable $source, callable $creator)
    {
        $list = new static();

        foreach ($source as $index => $value) {
            $list = $list->add($creator($value, $index));
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
            foreach ($this->modifiers as [$type, $callback]) {
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

    public function getIterator(): iterable
    {
        // todo try to optimize - there are 2 loops for iterating and applying modifiers
        $this->applyModifiers();

        yield from $this->listArray;
    }

    /**
     * @return static
     */
    public function add(mixed $value)
    {
        $this->applyModifiers();
        $list = clone $this;

        $list->listArray[] = $value;

        return $list;
    }

    /**
     * @return static
     */
    public function unshift(mixed $value)
    {
        $this->applyModifiers();
        $list = clone $this;

        array_unshift($list->listArray, $value);

        return $list;
    }

    public function first(): mixed
    {
        $this->applyModifiers();

        return empty($this->listArray)
            ? null
            : reset($this->listArray);
    }

    /**
     * @param callable $callback (value:mixed,index:int):bool
     */
    public function firstBy(callable $callback): mixed
    {
        $this->applyModifiers();

        foreach ($this->listArray as $i => $v) {
            if ($callback($v, $i)) {
                return $v;
            }
        }

        return null;
    }

    public function last(): mixed
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

    public function contains(mixed $value): bool
    {
        $this->applyModifiers();

        return $this->find($value) !== false;
    }

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     */
    public function containsBy(callable $callback): bool
    {
        foreach ($this as $i => $v) {
            if ($callback($v, $i) === true) {
                return true;
            }
        }

        return false;
    }

    protected function find(mixed $value): int|false
    {
        $this->applyModifiers();

        $index = array_search($value, $this->listArray, true);
        if (is_string($index)) {
            throw new LogicException(sprintf('List must have only integer indexes, but has "%s".', $index));
        }

        return $index;
    }

    /**
     * @return IList
     */
    public function removeFirst(mixed $value)
    {
        $this->applyModifiers();
        $index = $this->find($value);

        return $index !== false
            ? $this->removeIndex($index)
            : $this;
    }

    private function removeIndex(int $index): IList
    {
        $this->applyModifiers();
        $list = clone $this;

        unset($list->listArray[$index]);

        return static::from($list->listArray);
    }

    /**
     * @return static
     */
    public function removeAll(mixed $value)
    {
        $this->applyModifiers();

        return $this->filter(function ($item) use ($value) {
            return $item !== $value;
        });
    }

    /** @param callable $callback (value:mixed,index:int):void */
    public function each(callable $callback): void
    {
        foreach ($this as $i => $value) {
            $callback($value, $i);
        }
    }

    /**
     * @param callable $callback (value:mixed,index:int):mixed
     * @return static
     */
    public function map(callable $callback)
    {
        $list = clone $this;
        $list->modifiers[] = [self::MAP, $callback];

        return $list;
    }

    /**
     * @param callable $callback (value:mixed,index:int):bool
     * @return static
     */
    public function filter(callable $callback)
    {
        $list = clone $this;
        $list->modifiers[] = [self::FILTER, $callback];

        return $list;
    }

    /**
     * @param callable $reducer (total:mixed,value:mixed,index:int,list:IList):mixed
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $total = $initialValue;

        foreach ($this as $i => $value) {
            $total = $reducer($total, $value, $i, $this);
        }

        return $total;
    }

    public function clear()
    {
        return new static();
    }

    public function isEmpty(): bool
    {
        $this->applyModifiers();

        return empty($this->listArray);
    }

    /** @return \MF\Collection\Mutable\IList */
    public function asMutable()
    {
        return \MF\Collection\Mutable\ListCollection::from($this->toArray());
    }

    public function implode(string $glue): string
    {
        $this->applyModifiers();

        return implode($glue, $this->listArray);
    }
}
