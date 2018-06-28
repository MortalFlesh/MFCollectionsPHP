<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Parser\CallbackParser;
use MF\Validator\TypeValidator;

class ListCollection extends \MF\Collection\Mutable\ListCollection implements IList
{
    /** @var array */
    private $allowedValueTypes = [
        TypeValidator::TYPE_STRING,
        TypeValidator::TYPE_INT,
        TypeValidator::TYPE_FLOAT,
        TypeValidator::TYPE_BOOL,
        TypeValidator::TYPE_ARRAY,
        TypeValidator::TYPE_OBJECT,
        TypeValidator::TYPE_INSTANCE_OF,
    ];

    /** @var CallbackParser */
    private $callbackParser;

    /** @var TypeValidator */
    private $typeValidator;

    /**
     * @param string $TValue
     * @param <TValue> $values
     * @return static
     */
    public static function ofT(string $TValue, ...$values)
    {
        return static::fromT($TValue, $values);
    }

    /**
     * @param string $TValue
     * @param array $array
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
     * @param string $TValue
     * @param iterable $source <TValue>
     * @param callable $creator (value:mixed,index:int):TValue
     * @return IList<TValue>
     */
    public static function createT(string $TValue, iterable $source, $creator)
    {
        $list = new static($TValue);
        $creator = $list->callbackParser->parseArrowFunction($creator);

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
    public static function of(...$values)
    {
        throw new \BadMethodCallException(
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
        throw new \BadMethodCallException(
            'This method should not be used with Generic List. Use fromT instead.'
        );
    }

    /**
     * @deprecated
     * @see IList::createT()
     * @param mixed $creator
     * @return IList
     */
    public static function create(iterable $source, $creator)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Generic List. Use createT instead.'
        );
    }

    public function __construct(string $TValue)
    {
        $this->typeValidator = new TypeValidator(
            TypeValidator::TYPE_INT,
            $TValue,
            [TypeValidator::TYPE_INT],
            $this->allowedValueTypes
        );

        parent::__construct();
        $this->callbackParser = new CallbackParser();
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
     * @param <TValue> $value
     */
    public function add($value): void
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        parent::add($value);
    }

    /**
     * @param <TValue> $value
     */
    public function unshift($value): void
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        parent::unshift($value);
    }

    /**
     * @param <TValue> $value
     * @return bool
     */
    public function contains($value): bool
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::contains($value);
    }

    /**
     * @param <TValue> $value
     */
    public function removeFirst($value): void
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        parent::removeFirst($value);
    }

    /**
     * @param <TValue> $value
     */
    public function removeAll($value): void
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
     * @param string|null $TValue
     * @return IList
     */
    public function map($callback, string $TValue = null)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        $list = clone $this;
        $list->modifiers[] = [self::MAP, $callback, $TValue];

        return $list;
    }

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return IList
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        $list = clone $this;
        $list->modifiers[] = [self::FILTER, $callback];

        return $list;
    }

    /**
     * @param callable $reducer (total:<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|<RValue> $initialValue
     * @return <RValue>|<TValue>
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

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
