<?php declare(strict_types=1);

namespace MF\Collection\Generic;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @param <TValue> $values
     * @return IList
     */
    public static function ofT(string $TValue, ...$values);

    /**
     * @param array $array <TValue>
     * @return IList<TValue>
     */
    public static function fromT(string $TValue, array $array);

    /**
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
     * @param callable|string $callback (value:<TValue>,index:int):bool
     */
    public function containsBy($callback): bool;

    /**
     * @param callable|string $callback (value:<TValue>,index:int):<TValue>
     * @return IList<TValue>
     */
    public function map($callback, string $TValue = null);

    /**
     * @param callable|string $callback (value:<TValue>,index:int):bool
     * @return IList<TValue>
     */
    public function filter($callback);

    /**
     * @param callable|string $reducer (total:<RValue>|<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|<RValue> $initialValue
     * @return <RValue>|<TValue>
     */
    public function reduce($reducer, $initialValue = null);
}
