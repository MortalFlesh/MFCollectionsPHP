<?php

namespace MF\Collection\Immutable\Generic;

use MF\Parser\CallbackParser;
use MF\Validator\TypeValidator;

class Map extends \MF\Collection\Immutable\Map implements IMap
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
     * @param string $TKey
     * @param string $TValue
     * @param array $array
     * @return static
     */
    public static function ofKT(string $TKey, string $TValue, array $array)
    {
        $map = new static($TKey, $TValue);

        foreach ($array as $key => $value) {
            $map = $map->set($key, $value);
        }

        return $map;
    }

    /**
     * @deprecated
     * @see IMap::ofKT()
     */
    public static function of(array $array, bool $recursive = false)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Generic Map. Use ofKT instead.'
        );
    }

    public function __construct(string $TKey, string $TValue)
    {
        $this->typeValidator = new TypeValidator(
            $TKey,
            $TValue,
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
    public function containsKey($key): bool
    {
        $this->typeValidator->assertKeyType($key);

        return parent::containsKey($key);
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
     * @param callable $callback (key:<TKey>,value:<TValue>):<TValue>
     * @param string|null $TValue
     * @return static
     */
    public function map($callback, $TValue = null)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $map = new static($this->typeValidator->getKeyType(), $TValue ?: $this->typeValidator->getValueType());

        return $this->mapToMap($map, $callback);
    }

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):bool
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);
        $map = new static($this->typeValidator->getKeyType(), $this->typeValidator->getValueType());

        return $this->filterToMap($map, $callback);
    }

    /**
     * @return IList<TKey>
     */
    public function keys()
    {
        return ListCollection::ofT(
            $this->typeValidator->getKeyType(),
            array_keys($this->mapArray)
        );
    }

    /**
     * @return IList<TValue>
     */
    public function values()
    {
        return ListCollection::ofT(
            $this->typeValidator->getValueType(),
            array_values($this->mapArray)
        );
    }

    /**
     * @param callable $reducer (total:<RValue>|<TValue>,value:<TValue>,index:<TKey>,map:Map):<RValue>|<TValue>
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
        return new static($this->typeValidator->getKeyType(), $this->typeValidator->getValueType());
    }

    /**
     * @return \MF\Collection\Mutable\Generic\IMap
     */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Generic\Map::ofKT(
            $this->typeValidator->getKeyType(),
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
