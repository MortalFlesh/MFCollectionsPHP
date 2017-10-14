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

    public static function fromT(string $TValue, array $array): IList
    {
        $list = new static($TValue);

        foreach ($array as $item) {
            $list = $list->add($item);
        }

        return $list;
    }

    /**
     * @deprecated
     * @see IList::fromT()
     */
    public static function from(array $array, bool $recursive = false)
    {
        throw new \BadMethodCallException(
            'This method should not be used with Immutable Generic List. Use fromT instead.'
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

    protected function applyModifiers(): void
    {
        if (empty($this->modifiers) || empty($this->listArray)) {
            return;
        }

        $listArray = [];
        foreach ($this->listArray as $i => $value) {
            foreach ($this->modifiers as $item) {
                list($type, $callback) = $item;

                $TValue = $item[self::INDEX_TVALUE] ?? null;
                if ($TValue && $this->typeValidator->getValueType() !== $TValue) {
                    $this->typeValidator->changeValueType($TValue);
                }

                if ($type === self::MAP) {
                    $value = $callback($value, $i);
                } elseif ($type === self::FILTER && !$callback($value, $i)) {
                    continue 2;
                }
            }

            $this->typeValidator->assertValueType($value);
            $listArray[] = $value;
        }

        $this->listArray = $listArray;
        $this->modifiers = [];
    }

    /**
     * @param <TValue> $value
     * @return static
     */
    public function add($value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::add($value);
    }

    /**
     * @param <TValue> $value
     * @return static
     */
    public function unshift($value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        return parent::unshift($value);
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
     * @return IList
     */
    public function removeFirst($value)
    {
        $this->applyModifiers();
        $this->typeValidator->assertValueType($value);

        $index = $this->find($value);

        if ($index !== false) {
            return $this->removeIndex((int) $index);
        }

        return $this;
    }

    private function removeIndex(int $index): IList
    {
        $this->applyModifiers();
        $list = clone $this;

        unset($list->listArray[$index]);

        return static::fromT($list->typeValidator->getValueType(), $list->listArray);
    }

    /**
     * @param <TValue> $value
     * @return static
     */
    public function removeAll($value)
    {
        $this->applyModifiers();
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
        $callback = $this->callbackParser->parseArrowFunction($callback);

        $list = clone $this;
        $list->modifiers[] = [self::MAP, $callback, $TValue];

        return $list;
    }

    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):bool
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
        $this->applyModifiers();

        return new static($this->typeValidator->getValueType());
    }

    /** @return \MF\Collection\Mutable\Generic\IList */
    public function asMutable()
    {
        $this->applyModifiers();

        return \MF\Collection\Mutable\Generic\ListCollection::fromT(
            $this->typeValidator->getValueType(),
            $this->toArray()
        );
    }
}
