<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

interface IList extends \MF\Collection\Immutable\IList, \MF\Collection\Generic\IList
{
    /**
     * @param string $TValue
     * @param <TValue> $values
     * @return IList
     */
    public static function ofT(string $TValue, ...$values);

    /**
     * @param string $TValue
     * @param array $array <TValue>
     * @return IList<TValue>
     */
    public static function fromT(string $TValue, array $array);

    /**
     * @param string $TValue
     * @param iterable $source <TValue>
     * @param callable|string $creator (value:mixed,index:int):TValue
     * @return IList<TValue>
     */
    public static function createT(string $TValue, iterable $source, $creator);

    /**
     * @deprecated
     * @see IList::ofT()
     */
    public static function of(...$values);

    /**
     * @deprecated
     * @see IList::fromT()
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @deprecated
     * @see IList::createT()
     * @param mixed $creator
     */
    public static function create(iterable $source, $creator);

    /**
     * @param mixed $value
     * @return IList
     */
    public function add($value);

    /**
     * @param mixed $value
     * @return IList
     */
    public function unshift($value);

    /**
     * @param mixed $value
     * @return IList
     */
    public function removeFirst($value);

    /**
     * @param mixed $value
     * @return IList
     */
    public function removeAll($value);

    /** @return IList */
    public function clear();

    /** @return IList */
    public function sort();

    /**
     * @param callable|string $callback (value:<TValue>,index:int):<TValue>
     * @param string|null $TValue
     * @return IList<TValue>
     */
    public function map($callback, string $TValue = null);

    /**
     * @param callable|string $callback (value:<TValue>,index:int):bool
     * @return IList<TValue>
     */
    public function filter($callback);

    /** @return \MF\Collection\Mutable\Generic\IList */
    public function asMutable();
}
