<?php

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Generic\IList;
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

    public static function ofT(string $valueType, array $array)
    {
        $list = new static($valueType);

        foreach ($array as $item) {
            $list->add($item);
        }

        return $list;
    }

    /**
     * @param array $array
     * @param bool $recursive
     * @return static
     */
    public static function of(array $array, $recursive = false)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Generic List. Use ofT instead.'
        );
    }

    /**
     * @param string $valueType
     */
    public function __construct($valueType)
    {
        $this->typeValidator = new TypeValidator(
            TypeValidator::TYPE_INT,
            $valueType,
            [TypeValidator::TYPE_INT],
            $this->allowedValueTypes
        );

        parent::__construct();
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param <TValue> $value
     */
    public function add($value)
    {
        $this->typeValidator->assertValueType($value);

        parent::add($value);
    }

    /**
     * @param <TValue> $value
     */
    public function unshift($value)
    {
        $this->typeValidator->assertValueType($value);

        parent::unshift($value);
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
     */
    public function removeFirst($value)
    {
        $this->typeValidator->assertValueType($value);

        parent::removeFirst($value);
    }

    /**
     * @param <TValue> $value
     */
    public function removeAll($value)
    {
        $this->typeValidator->assertValueType($value);

        parent::removeAll($value);
    }

    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):<TValue>
     * @param string|null $mappedListValueType
     * @return \MF\Collection\Mutable\IList|static
     */
    public function map($callback, $mappedListValueType = null)
    {
        if (isset($mappedListValueType)) {
            $list = new static($mappedListValueType);
        } else {
            $list = new \MF\Collection\Mutable\Enhanced\ListCollection();
        }

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
     * @param callable $reducer (total:<TValue>,value:<TValue>,index:<TKey>,list:List):<TValue>
     * @param null|<TValue> $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        if (!is_null($initialValue)) {
            $this->typeValidator->assertValueType($initialValue);
        }

        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return \MF\Collection\Immutable\Generic\ListCollection
     */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\Generic\ListCollection::ofT(
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
