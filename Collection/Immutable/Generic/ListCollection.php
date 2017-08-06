<?php

namespace MF\Collection\Immutable\Generic;

use MF\Parser\CallbackParser;
use MF\Validator\TypeValidator;

class ListCollection extends \MF\Collection\Immutable\ListCollection implements IList
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

    public static function ofT(string $TValue, array $array): IList
    {
        $list = new static($TValue);

        foreach ($array as $item) {
            $list = $list->add($item);
        }

        return $list;
    }

    /**
     * @deprecated
     * @see IList::ofT()
     */
    public static function of(array $array, bool $recursive = false)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Immutable Generic List. Use ofT instead.'
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

    /**
     * @param <TValue> $value
     * @return static
     */
    public function add($value)
    {
        $this->typeValidator->assertValueType($value);

        return parent::add($value);
    }

    /**
     * @param <TValue> $value
     * @return static
     */
    public function unshift($value)
    {
        $this->typeValidator->assertValueType($value);

        return parent::unshift($value);
    }

    /**
     * @param <TValue> $value
     * @return bool
     */
    public function contains($value): bool
    {
        $this->typeValidator->assertValueType($value);

        return parent::contains($value);
    }

    /**
     * @param <TValue> $value
     * @return IList
     */
    public function removeFirst($value)
    {
        $this->typeValidator->assertValueType($value);

        $index = $this->find($value);

        if ($index !== false) {
            return $this->removeIndex((int) $index);
        }

        return $this;
    }

    private function removeIndex(int $index): IList
    {
        $list = clone $this;

        unset($list->listArray[$index]);

        return static::ofT($list->typeValidator->getValueType(), $list->listArray);
    }

    /**
     * @param <TValue> $value
     * @return static
     */
    public function removeAll($value)
    {
        $this->typeValidator->assertValueType($value);

        return parent::removeAll($value);
    }

    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):<TValue>
     * @param string|null $TValue
     * @return static
     */
    public function map($callback, string $TValue = null)
    {
        $list = new static($TValue ?: $this->typeValidator->getValueType());

        $callback = $this->callbackParser->parseArrowFunction($callback);

        return $this->mapList($list, $callback);
    }

    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):bool
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $list = new static($this->typeValidator->getValueType());

        return $this->filterList($list, $callback);
    }

    /**
     * @param callable $reducer (total:<RValue>|<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|<RValue> $initialValue
     * @return <RValue>|<TValue>
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return static
     */
    public function clear()
    {
        return new static($this->typeValidator->getValueType());
    }

    /** @return \MF\Collection\Mutable\Generic\IList */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Generic\ListCollection::ofT(
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
