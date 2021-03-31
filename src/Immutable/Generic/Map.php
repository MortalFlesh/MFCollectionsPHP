<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\Exception\BadMethodCallException;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Immutable\Tuple;
use MF\Validator\TypeValidator;

class Map extends \MF\Collection\Immutable\Map implements IMap
{
    private const ALLOWED_KEY_TYPES = [
        TypeValidator::TYPE_ANY,
        TypeValidator::TYPE_MIXED,
        TypeValidator::TYPE_STRING,
        TypeValidator::TYPE_INT,
        TypeValidator::TYPE_FLOAT,
    ];

    private const ALLOWED_VALUE_TYPES = [
        TypeValidator::TYPE_ANY,
        TypeValidator::TYPE_MIXED,
        TypeValidator::TYPE_STRING,
        TypeValidator::TYPE_INT,
        TypeValidator::TYPE_FLOAT,
        TypeValidator::TYPE_BOOL,
        TypeValidator::TYPE_ARRAY,
        TypeValidator::TYPE_CALLABLE,
        TypeValidator::TYPE_OBJECT,
        TypeValidator::TYPE_INSTANCE_OF,
    ];

    private TypeValidator $typeValidator;

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
     * @param iterable $source T: <TKey,mixed>
     * @param callable $creator (value:mixed,key:TKey):TValue
     * @return static|IMap T: <TKey,TValue>
     */
    public static function createKT(string $TKey, string $TValue, iterable $source, callable $creator)
    {
        $map = new static($TKey, $TValue);

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
        throw new BadMethodCallException(
            'This method should not be used with Generic Map. Use fromKT instead.'
        );
    }

    /**
     * @deprecated
     * @see IMap::createKT()
     * @return IMap
     */
    public static function create(iterable $source, callable $creator)
    {
        throw new BadMethodCallException(
            'This method should not be used with Generic Map. Use createKT instead.'
        );
    }

    public function __construct(string $TKey, string $TValue)
    {
        $this->typeValidator = new TypeValidator(
            $TKey,
            $TValue,
            self::ALLOWED_KEY_TYPES,
            self::ALLOWED_VALUE_TYPES,
            InvalidArgumentException::class
        );

        parent::__construct();
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
     * @param mixed $key T: <TKey>
     */
    public function containsKey($key): bool
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        return parent::containsKey($key);
    }

    /**
     * @param mixed $value T: <TValue>
     */
    public function contains($value): bool
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::contains($value);
    }

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):bool
     */
    public function containsBy(callable $callback): bool
    {
        return parent::containsBy($callback);
    }

    /**
     * @param mixed $value T: <TValue>
     * @return mixed|false
     */
    public function find($value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::find($value);
    }

    /**
     * @param mixed $key T: <TKey>
     * @return mixed T: <TValue>
     */
    public function get($key)
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        return parent::get($key);
    }

    /**
     * @param mixed $key T: <TKey>
     * @param mixed $value T: <TValue>
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
     * @param mixed $key T: <TKey>
     * @return static
     */
    public function remove($key)
    {
        $this->applyModifiers();
        $this->typeValidator->assertKeyType($key);

        return parent::remove($key);
    }

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):<TValue>
     * @param string|null $TValue
     * @return static
     */
    public function map(callable $callback, $TValue = null)
    {
        $map = clone $this;
        $map->modifiers[] = Tuple::of(self::MAP, $callback, $TValue);

        return $map;
    }

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):bool
     * @return static
     */
    public function filter(callable $callback)
    {
        $list = clone $this;
        $list->modifiers[] = Tuple::of(self::FILTER, $callback);

        return $list;
    }

    /**
     * @return IList T: <TKey>
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
     * @return IList T: <TValue>
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
     * @param null|mixed $initialValue null|<RValue>
     * @return mixed <RValue>|<TValue>
     */
    public function reduce(callable $reducer, $initialValue = null)
    {
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
