<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Exception\BadMethodCallException;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Immutable\Tuple;
use MF\Validator\TypeValidator;

class ListCollection extends \MF\Collection\Immutable\ListCollection implements IList
{
    private const ALLOWED_VALUE_TYPES = [
        TypeValidator::TYPE_ANY,
        TypeValidator::TYPE_MIXED,
        TypeValidator::TYPE_STRING,
        TypeValidator::TYPE_INT,
        TypeValidator::TYPE_FLOAT,
        TypeValidator::TYPE_BOOL,
        TypeValidator::TYPE_ARRAY,
        TypeValidator::TYPE_CALLABLE,
        TypeValidator::TYPE_OBJECT,
        TypeValidator::TYPE_INSTANCE_OF,
    ];

    private TypeValidator $typeValidator;

    public static function ofT(string $TValue, ...$values)
    {
        return static::fromT($TValue, $values);
    }

    public static function fromT(string $TValue, array $array): IList
    {
        $list = new static($TValue);

        foreach ($array as $item) {
            $list = $list->add($item);
        }

        return $list;
    }

    /**
     * @param iterable $source T: <TValue>
     * @param callable $creator (value:mixed,index:int):TValue
     * @return IList T: <TValue>
     */
    public static function createT(string $TValue, iterable $source, callable $creator)
    {
        $list = new static($TValue);

        foreach ($source as $index => $value) {
            $list = $list->add($creator($value, $index));
        }

        return $list;
    }

    /**
     * @return IList
     * @see IList::ofT()
     * @deprecated
     */
    public static function of(mixed ...$values)
    {
        throw new BadMethodCallException(
            'This method should not be used with Immutable Generic List. Use ofT instead.'
        );
    }

    /**
     * @return IList
     * @see IList::fromT()
     * @deprecated
     */
    public static function from(array $array, bool $recursive = false)
    {
        throw new BadMethodCallException(
            'This method should not be used with Immutable Generic List. Use fromT instead.'
        );
    }

    /**
     * @return IList
     * @deprecated
     * @see IList::createT()
     */
    public static function create(iterable $source, callable $creator)
    {
        throw new BadMethodCallException(
            'This method should not be used with Generic List. Use createT instead.'
        );
    }

    public function __construct(string $TValue)
    {
        $this->typeValidator = new TypeValidator(
            TypeValidator::TYPE_INT,
            $TValue,
            [TypeValidator::TYPE_INT],
            self::ALLOWED_VALUE_TYPES,
            InvalidArgumentException::class
        );

        parent::__construct();
    }

    protected function applyModifiers(): void
    {
        if (empty($this->modifiers) || empty($this->listArray)) {
            $this->modifiers = [];

            return;
        }

        $listArray = [];
        foreach ($this->listArray as $i => $value) {
            foreach ($this->modifiers as $item) {
                [$type, $callback] = $item;

                $TValue = $item[self::INDEX_TVALUE] ?? null;
                if ($TValue && $this->typeValidator->getValueType() !== $TValue) {
                    $this->typeValidator->changeValueType($TValue);
                }

                if ($type === self::MAP) {
                    $value = $callback($value, $i);
                } elseif ($type === self::FILTER && !$callback($value, $i)) {
                    continue 2;
                }
            }

            $this->typeValidator->assertValueType($value);
            $listArray[] = $value;
        }

        $this->listArray = $listArray;
        $this->modifiers = [];
    }

    /**
     * @param mixed $value T: <TValue>
     * @return static
     */
    public function add(mixed $value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::add($value);
    }

    /**
     * @param mixed $value T: <TValue>
     * @return static
     */
    public function unshift($value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::unshift($value);
    }

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return mixed T: <TValue>
     */
    public function firstBy(callable $callback): mixed
    {
        return parent::firstBy($callback);
    }

    /**
     * @param mixed $value T: <TValue>
     */
    public function contains(mixed $value): bool
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::contains($value);
    }

    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):bool
     */
    public function containsBy(callable $callback): bool
    {
        return parent::containsBy($callback);
    }

    /**
     * @param mixed $value T: <TValue>
     * @return IList
     */
    public function removeFirst(mixed $value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

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

        return static::fromT($list->typeValidator->getValueType(), $list->listArray);
    }

    /**
     * @param mixed $value T: <TValue>
     * @return static
     */
    public function removeAll(mixed $value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::removeAll($value);
    }

    /**
     * @return static|IList
     */
    public function sort(): IList
    {
        $this->applyModifiers();
        $sortedMap = $this->listArray;
        sort($sortedMap);

        return static::fromT($this->typeValidator->getValueType(), $sortedMap);
    }

    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):<TValue>
     * @return static
     */
    public function map(callable $callback, string $TValue = null)
    {
        $list = clone $this;
        $list->modifiers[] = Tuple::of(self::MAP, $callback, $TValue);

        return $list;
    }

    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):bool
     * @return static
     */
    public function filter(callable $callback)
    {
        $list = clone $this;
        $list->modifiers[] = Tuple::of(self::FILTER, $callback);

        return $list;
    }

    /**
     * @param callable $reducer (total:<RValue>|<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|mixed $initialValue null|<RValue>
     * @return mixed <RValue>|<TValue>
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return static
     */
    public function clear()
    {
        $this->applyModifiers();

        return new static($this->typeValidator->getValueType());
    }

    /** @return \MF\Collection\Mutable\Generic\IList */
    public function asMutable()
    {
        $this->applyModifiers();

        return \MF\Collection\Mutable\Generic\ListCollection::fromT(
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
