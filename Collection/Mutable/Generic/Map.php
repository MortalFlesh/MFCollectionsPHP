<?php

namespace MF\Collection\Mutable\Generic;

use MF\Parser\CallbackParser;
use MF\Validator\TypeValidator;

class Map extends \MF\Collection\Mutable\Map implements IMap
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
    public static function fromKT(string $TKey, string $TValue, array $array)
    {
        $map = new static($TKey, $TValue);

        foreach ($array as $key => $value) {
            $map->set($key, $value);
        }

        return $map;
    }

    /**
     * @deprecated
     * @see IMap::fromKT()
     */
    public static function from(array $array, bool $recursive = false)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Generic Map. Use fromKT instead.'
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

    protected function applyModifiers(): void
    {
        if (empty($this->modifiers) || empty($this->mapArray)) {
            return;
        }

        $mapArray = [];
        foreach ($this->mapArray as $key => $value) {
            foreach ($this->modifiers as $item) {
                list($type, $callback) = $item;

                $TValue = $item[self::INDEX_TVALUE] ?? null;
                if ($TValue && $this->typeValidator->getValueType() !== $TValue) {
                    $this->typeValidator->changeValueType($TValue);
                }

                if ($type === self::MAP) {
                    $value = $callback($key, $value);
                } elseif ($type === self::FILTER && !$callback($key, $value)) {
                    continue 2;
                }
            }

            $this->typeValidator->assertValueType($value);
            $mapArray[$key] = $value;
        }

        $this->mapArray = $mapArray;
        $this->modifiers = [];
    }

    /**
     * @param <TKey> $key
     * @return bool
     */
    public function containsKey($key): bool
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        return parent::containsKey($key);
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
     * @return mixed|false
     */
    public function find($value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::find($value);
    }

    /**
     * @param <TKey> $key
     * @return <TValue>
     */
    public function get($key)
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        return parent::get($key);
    }

    /**
     * @param <TKey> $key
     * @param <TValue> $value
     */
    public function set($key, $value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);
        $this->typeValidator->assertValueType($value);

        parent::set($key, $value);
    }

    /**
     * @param <TKey> $key
     */
    public function remove($key)
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        parent::remove($key);
    }

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):<TValue>
     * @param string|null $TValue
     * @return static
     */
    public function map($callback, $TValue = null)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        $map = clone $this;
        $map->modifiers[] = [self::MAP, $callback, $TValue];

        return $map;
    }

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):bool
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        $list = clone $this;
        $list->modifiers[] = [self::FILTER, $callback];

        return $list;
    }

    /**
     * @return IList<TKey>
     */
    public function keys()
    {
        $this->applyModifiers();

        return ListCollection::fromT(
            $this->typeValidator->getKeyType(),
            array_keys($this->mapArray)
        );
    }

    /**
     * @return IList<TValue>
     */
    public function values()
    {
        $this->applyModifiers();

        return ListCollection::fromT(
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
        $this->applyModifiers();
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return \MF\Collection\Immutable\Generic\IMap
     */
    public function asImmutable()
    {
        $this->applyModifiers();

        return \MF\Collection\Immutable\Generic\Map::fromKT(
            $this->typeValidator->getKeyType(),
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
