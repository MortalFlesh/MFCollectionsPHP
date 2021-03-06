<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Exception\BadMethodCallException;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Validator\TypeValidator;

class ListCollection extends \MF\Collection\Mutable\ListCollection implements IList
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

    /**
     * @param mixed $values T: <TValue>
     * @return static
     */
    public static function ofT(string $TValue, mixed ...$values)
    {
        return static::fromT($TValue, $values);
    }

    /**
     * @return static
     */
    public static function fromT(string $TValue, array $array)
    {
        $list = new static($TValue);

        foreach ($array as $item) {
            $list->add($item);
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
            $list->add($creator($value, $index));
        }

        return $list;
    }

    /**
     * @deprecated
     * @see IList::ofT()
     * @return IList
     */
    public static function of(mixed ...$values)
    {
        throw new BadMethodCallException(
            'This method should not be used with Generic List. Use ofT instead.'
        );
    }

    /**
     * @deprecated
     * @see IList::fromT()
     * @return IList
     */
    public static function from(array $array, bool $recursive = false)
    {
        throw new BadMethodCallException(
            'This method should not be used with Generic List. Use fromT instead.'
        );
    }

    /**
     * @deprecated
     * @see IList::createT()
     * @return IList
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
     */
    public function add(mixed $value): void
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        parent::add($value);
    }

    /**
     * @param mixed $value T: <TValue>
     */
    public function unshift(mixed $value): void
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        parent::unshift($value);
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
     * @param callable $callback (value:<TValue>,index:int):bool
     */
    public function containsBy(callable $callback): bool
    {
        return parent::containsBy($callback);
    }

    /**
     * @param mixed $value T: <TValue>
     */
    public function removeFirst(mixed $value): void
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        parent::removeFirst($value);
    }

    /**
     * @param mixed $value T: <TValue>
     */
    public function removeAll(mixed $value): void
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        parent::removeAll($value);
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
     * @param callable $callback (value:<TValue>,index:int):<TValue>
     * @return IList
     */
    public function map(callable $callback, string $TValue = null)
    {
        $list = clone $this;
        $list->modifiers[] = [self::MAP, $callback, $TValue];

        return $list;
    }

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return IList
     */
    public function filter(callable $callback)
    {
        $list = clone $this;
        $list->modifiers[] = [self::FILTER, $callback];

        return $list;
    }

    /**
     * @param callable $reducer (total:<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|mixed $initialValue null|<RValue>
     * @return mixed <RValue>|<TValue>
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $total = $initialValue;

        foreach ($this as $i => $value) {
            $total = $reducer($total, $value, $i, $this);
        }

        return $total;
    }

    /**
     * @return \MF\Collection\Immutable\Generic\IList
     */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\Generic\ListCollection::fromT(
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
