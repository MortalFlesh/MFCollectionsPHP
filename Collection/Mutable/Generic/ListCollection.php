<?php

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
     * @param array $array
     * @return static
     */
    public static function ofT(string $TValue, array $array)
    {
        $list = new static($TValue);

        foreach ($array as $item) {
            $list->add($item);
        }

        return $list;
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
     * @param callable $callback (value:<TValue>,index:int):<TValue>
     * @param string|null $TValue
     * @return IList
     */
    public function map($callback, string $TValue = null)
    {
        $list = new static($TValue ?: $this->typeValidator->getValueType());

        $callback = $this->callbackParser->parseArrowFunction($callback);

        return $this->mapList($list, $callback);
    }

    /**
     * @param IList $list
     * @param callable $callback
     * @return IList
     */
    private function mapList(IList $list, callable $callback)
    {
        foreach ($this as $i => $value) {
            $list->add($callback($value, $i));
        }

        return $list;
    }

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return IList
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $list = new static($this->typeValidator->getValueType());

        return $this->filterList($list, $callback);
    }

    /**
     * @param IList $list
     * @param callable $callback
     * @return IList
     */
    private function filterList(IList $list, callable $callback)
    {
        $this->assertCallback($callback);

        foreach ($this as $i => $value) {
            if ($callback($value, $i)) {
                $list->add($value);
            }
        }

        return $list;
    }

    /**
     * @param callable $reducer (total:<TValue>,value:<TValue>,index:int,list:IList):<TValue>
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
     * @deprecated
     * @see IList::ofT()
     */
    public static function of(array $array, bool $recursive = false): \MF\Collection\Mutable\IList
    {
        throw new \BadMethodCallException(
            'This method should not be used with Generic List. Use ofT instead.'
        );
    }

    /**
     * @return \MF\Collection\Immutable\Generic\IList
     */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\Generic\ListCollection::ofT(
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
