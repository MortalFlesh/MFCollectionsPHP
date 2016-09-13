<?php

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Generic\MapInterface;
use MF\Parser\CallbackParser;
use MF\Validator\TypeValidator;

class Map extends \MF\Collection\Immutable\Map implements MapInterface
{
    /** @var array */
    private $allowedKeyTypes = [
        TypeValidator::TYPE_STRING,
        TypeValidator::TYPE_INT,
        TypeValidator::TYPE_FLOAT,
    ];

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
     * @param string $keyType
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericFromArray($keyType, $valueType, array $array)
    {
        $map = new static($keyType, $valueType);

        foreach ($array as $key => $value) {
            $map = $map->set($key, $value);
        }

        return $map;
    }

    /**
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericListFromArray($valueType, array $array)
    {
        throw new \BadMethodCallException('This method should not be used with Generic Map. Use createGenericFromArray instead.');
    }

    /**
     * @param array $array
     * @param bool $recursive
     * @return static
     */
    public static function createFromArray(array $array, $recursive = false)
    {
        throw new \BadMethodCallException('This method should not be used with Generic Map. Use createGenericFromArray instead.');
    }

    /**
     * @param string $keyType
     * @param string $valueType
     */
    public function __construct($keyType, $valueType)
    {
        $this->typeValidator = new TypeValidator(
            $keyType,
            $valueType,
            $this->allowedKeyTypes,
            $this->allowedValueTypes
        );

        parent::__construct();
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param <TKey> $key
     * @return bool
     */
    public function containsKey($key)
    {
        $this->typeValidator->assertKeyType($key);

        return parent::containsKey($key);
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
     * @return mixed|false
     */
    public function find($value)
    {
        $this->typeValidator->assertValueType($value);

        return parent::find($value);
    }

    /**
     * @param <TKey> $key
     * @return <TValue>
     */
    public function get($key)
    {
        $this->typeValidator->assertKeyType($key);

        return parent::get($key);
    }

    /**
     * @param <TKey> $key
     * @param <TValue> $value
     * @return static
     */
    public function set($key, $value)
    {
        $this->typeValidator->assertKeyType($key);
        $this->typeValidator->assertValueType($value);

        return parent::set($key, $value);
    }

    /**
     * @param <TKey> $key
     * @return static
     */
    public function remove($key)
    {
        $this->typeValidator->assertKeyType($key);

        return parent::remove($key);
    }

    /**
     * @param callable (key:<TKey>,value:<TValue>):<TValue> $callback
     * @param string|null $mappedMapValueType
     * @return \MF\Collection\Immutable\MapInterface|static
     */
    public function map($callback, $mappedMapValueType = null)
    {
        if (isset($mappedMapValueType)) {
            $map = new static($this->typeValidator->getKeyType(), $mappedMapValueType);
        } else {
            $map = new \MF\Collection\Immutable\Enhanced\Map();
        }

        $callback = $this->callbackParser->parseArrowFunction($callback);

        return $this->mapToMap($map, $callback);
    }

    /**
     * @param callable (key:<TKey>,value:<TValue>):bool $callback
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $map = new static($this->typeValidator->getKeyType(), $this->typeValidator->getValueType());

        return $this->filterToMap($map, $callback);
    }

    /**
     * @return ListCollection<TKey>
     */
    public function keys()
    {
        return ListCollection::createGenericListFromArray(
            $this->typeValidator->getKeyType(),
            array_keys($this->mapArray)
        );
    }

    /**
     * @return ListCollection<TValue>
     */
    public function values()
    {
        return ListCollection::createGenericListFromArray(
            $this->typeValidator->getValueType(),
            array_values($this->mapArray)
        );
    }

    /**
     * @param callable (total:<TValue>,value:<TValue>,index:<TKey>,map:Map):<TValue> $reducer
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
     * @return \MF\Collection\Mutable\Generic\Map
     */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Generic\Map::createGenericFromArray(
            $this->typeValidator->getKeyType(),
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }

    /**
     * @return static
     */
    public function clear()
    {
        return new static($this->typeValidator->getKeyType(), $this->typeValidator->getValueType());
    }
}
