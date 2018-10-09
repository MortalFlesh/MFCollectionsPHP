<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Parser\CallbackParser;
use MF\Validator\TypeValidator;

class Map extends \MF\Collection\Immutable\Map implements IMap
{
    /** @var array */
    private $allowedKeyTypes = [
        TypeValidator::TYPE_ANY,
        TypeValidator::TYPE_MIXED,
        TypeValidator::TYPE_STRING,
        TypeValidator::TYPE_INT,
        TypeValidator::TYPE_FLOAT,
    ];

    /** @var array */
    private $allowedValueTypes = [
        TypeValidator::TYPE_ANY,
        TypeValidator::TYPE_MIXED,
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
     * @return static
     */
    public static function fromKT(string $TKey, string $TValue, array $array)
    {
        $map = new static($TKey, $TValue);

        foreach ($array as $key => $value) {
            $map = $map->set($key, $value);
        }

        return $map;
    }

    /**
     * @param iterable $source <TKey,mixed>
     * @param callable|string $creator (value:mixed,key:TKey):TValue
     * @return static|IMap<TKey,TValue>
     */
    public static function createKT(string $TKey, string $TValue, iterable $source, $creator)
    {
        $map = new static($TKey, $TValue);
        $creator = $map->callbackParser->parseArrowFunction($creator);

        foreach ($source as $key => $value) {
            $map = $map->set($key, $creator($value, $key));
        }

        return $map;
    }

    /**
     * @deprecated
     * @see IMap::fromKT()
     * @return IMap
     */
    public static function from(array $array, bool $recursive = false)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Generic Map. Use fromKT instead.'
        );
    }

    /**
     * @deprecated
     * @see IMap::createKT()
     * @param mixed $creator
     * @return IMap
     */
    public static function create(iterable $source, $creator)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Generic Map. Use createKT instead.'
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
                [$type, $callback] = $item;

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
     */
    public function containsKey($key): bool
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        return parent::containsKey($key);
    }

    /**
     * @param <TValue> $value
     */
    public function contains($value): bool
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::contains($value);
    }

    /**
     * @param callable|string $callback (key:<TKey>,value:<TValue>):bool
     */
    public function containsBy($callback): bool
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::containsBy($callback);
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
     * @return static
     */
    public function set($key, $value)
    {
        $this->applyModifiers();
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
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        return parent::remove($key);
    }

    /**
     * @param callable|string $callback (key:<TKey>,value:<TValue>):<TValue>
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
     * @param callable|string $callback (key:<TKey>,value:<TValue>):bool
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
     * @param callable|string $reducer (total:<RValue>|<TValue>,value:<TValue>,index:<TKey>,map:Map):<RValue>|<TValue>
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
        return \MF\Collection\Mutable\Generic\Map::fromKT(
            $this->typeValidator->getKeyType(),
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
