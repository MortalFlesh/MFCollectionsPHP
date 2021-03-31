<?php declare(strict_types=1);

namespace MF\Collection\Generic;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @param mixed $values T: <TValue>
     * @return IList
     */
    public static function ofT(string $TValue, mixed ...$values);

    /**
     * @param array $array T: <TValue>
     * @return IList T: <TValue>
     */
    public static function fromT(string $TValue, array $array);

    /**
     * @param iterable $source T: <TValue>
     * @param callable $creator (value:mixed,index:int):TValue
     * @return IList T: <TValue>
     */
    public static function createT(string $TValue, iterable $source, callable $creator);

    /**
     * @deprecated
     * @see IList::ofT()
     */
    public static function of(mixed ...$values);

    /**
     * @deprecated
     * @see IList::fromT()
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @deprecated
     * @see IList::createT()
     */
    public static function create(iterable $source, callable $creator);

    /**
     * @return mixed T: <TValue>
     */
    public function first(): mixed;

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return mixed T: <TValue>
     */
    public function firstBy(callable $callback): mixed;

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     */
    public function containsBy(callable $callback): bool;

    /**
     * @param callable $callback (value:<TValue>,index:int):<TValue>
     * @return IList T: <TValue>
     */
    public function map(callable $callback, string $TValue = null);

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return IList T: <TValue>
     */
    public function filter(callable $callback);

    /**
     * @param callable $reducer (total:<RValue>|<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|mixed $initialValue null|<RValue>
     * @return mixed <RValue>|<TValue>
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed;
}
