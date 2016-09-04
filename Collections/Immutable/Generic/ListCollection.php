<?php

namespace MFCollections\Collections\Immutable\Generic;

use MFCollections\Collections\Generic\CollectionInterface;
use MFCollections\Collections\Generic\ListInterface;
use MF\Parser\CallbackParser;
use MFCollections\Services\Validators\TypeValidator;

class ListCollection extends \MFCollections\Collections\Immutable\ListCollection implements CollectionInterface, ListInterface
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
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericListFromArray($valueType, array $array)
    {
        $list = new static($valueType);

        foreach ($array as $item) {
            $list = $list->add($item);
        }

        return $list;
    }

    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericFromArray($keyType, $valueType, array $array)
    {
        throw new \BadMethodCallException('This method should not be used with Immutable Generic List. Use createGenericListFromArray insted.');
    }

    public static function createFromArray(array $array, $recursive = false)
    {
        throw new \BadMethodCallException('This method should not be used with Immutable Generic List. Use createGenericListFromArray insted.');
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
    public function contains($value)
    {
        $this->typeValidator->assertValueType($value);

        return parent::contains($value);
    }

    /**
     * @param <TValue> $value
     * @return static
     */
    public function removeFirst($value)
    {
        $this->typeValidator->assertValueType($value);

        $index = $this->find($value);

        if ($index !== false) {
            return $this->removeIndex($index);
        }

        return $this;
    }

    /**
     * @param int $index
     * @return static
     */
    private function removeIndex($index)
    {
        $list = clone $this;

        unset($list->listArray[$index]);

        return static::createGenericListFromArray($list->typeValidator->getValueType(), $list->listArray);
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
     * @param callable (value:<TValue>,index:<TKey>):<TValue> $callback
     * @param string|null $mappedListValueType
     * @return \MFCollections\Collections\Immutable\ListInterface|static
     */
    public function map($callback, $mappedListValueType = null)
    {
        if (isset($mappedListValueType)) {
            $list = new static($mappedListValueType);
        } else {
            $list = new \MFCollections\Collections\Immutable\Enhanced\ListCollection();
        }

        $callback = $this->callbackParser->parseArrowFunction($callback);

        return $this->mapList($list, $callback);
    }

    /**
     * @param callable (value:<TValue>,index:<TKey>):bool $callback
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $list = new static($this->typeValidator->getValueType());

        return $this->filterList($list, $callback);
    }

    /**
     * @param callable (total:<TValue>,value:<TValue>,index:<TKey>,list:List):<TValue> $reducer
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
     * @return \MFCollections\Collections\Generic\ListCollection
     */
    public function asMutable()
    {
        return \MFCollections\Collections\Generic\ListCollection::createGenericListFromArray(
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
