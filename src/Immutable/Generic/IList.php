<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

interface IList extends \MF\Collection\Immutable\IList, \MF\Collection\Generic\IList
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
     * @return IList
     */
    public function add(mixed $value);

    /**
     * @return IList
     */
    public function unshift(mixed $value);

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
     * @return IList
     */
    public function removeFirst(mixed $value);

    /**
     * @return IList
     */
    public function removeAll(mixed $value);

    /** @return IList */
    public function clear();

    /** @return IList */
    public function sort();

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

    /** @return \MF\Collection\Mutable\Generic\IList */
    public function asMutable();
}
