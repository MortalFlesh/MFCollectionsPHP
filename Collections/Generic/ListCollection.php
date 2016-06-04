<?php

namespace MFCollections\Collections\Generic;

use MFCollections\Collections\ListCollection as BaseListCollection;
use MFCollections\Services\Parsers\CallbackParser;
use MFCollections\Services\Validators\TypeValidator;

class ListCollection extends BaseListCollection implements CollectionGenericInterface
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
            $list->add($item);
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
        throw new \BadMethodCallException('This method should not be used with Generic List. Use createGenericListFromArray insted.');
    }

    /**
     * @param array $array
     * @param bool $recursive
     * @return static
     */
    public static function createFromArray(array $array, $recursive = false)
    {
        throw new \BadMethodCallException('This method should not be used with Generic List. Use createGenericListFromArray insted.');
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
    public function contains($value)
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
     * @param callable $callback
     * @return static
     */
    public function map($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $list = new static($this->typeValidator->getValueType());

        return $this->mapList($list, $callback);
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $list = new static($this->typeValidator->getValueType());

        return $this->filterList($list, $callback);
    }
}
