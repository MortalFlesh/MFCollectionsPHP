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
     * @param callable $creator (value:mixed,index:int):TValue
     * @return IList<TValue>
     */
    public static function createT(string $TValue, iterable $source, callable $creator);

    /**
     * @deprecated
     * @see IList::ofT()
     */
    public static function of(...$values);

    /**
     * @deprecated
     * @see IList::fromT()
     */
    public static function from(array $array, bool $recursive = false): IList;

    /**
     * @deprecated
     * @see IList::createT()
     */
    public static function create(iterable $source, callable $creator);

    /**
     * @return <TValue>
     */
    public function first();

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return <TValue>
     */
    public function firstBy($callback);

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     */
    public function containsBy(callable $callback): bool;

    /**
     * @param callable $callback (value:<TValue>,index:int):<TValue>
     * @return IList<TValue>
     */
    public function map(callable $callback, string $TValue = null);

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return IList<TValue>
     */
    public function filter(callable $callback);

    /**
     * @param callable $reducer (total:<RValue>|<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|<RValue> $initialValue
     * @return <RValue>|<TValue>
     */
    public function reduce(callable $reducer, $initialValue = null);
}
