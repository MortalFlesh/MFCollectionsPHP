<?php declare(strict_types=1);

namespace MF\Collection\Generic;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /**
     * @param array $array T: <TKey, TValue>
     * @return IMap T: <TKey, TValue>
     */
    public static function fromKT(string $TKey, string $TValue, array $array);

    /**
     * @param callable $creator (value:mixed,key:TKey):TValue
     * @return IMap T: <TKey, TValue>
     */
    public static function createKT(string $TKey, string $TValue, iterable $source, callable $creator);

    /**
     * @deprecated
     * @see IMap::fromKT()
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @deprecated
     * @see IMap::createKT()
     */
    public static function create(iterable $source, callable $creator);

    /** @return IList T: <TKey> */
    public function keys();

    /** @return IList T: <TValue> */
    public function values();

    /**
     * @param callable $callback (value:TValue,key:TKey):bool
     */
    public function containsBy(callable $callback): bool;

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):<TValue>
     * @return IMap T: <TKey, TValue>
     */
    public function map(callable $callback, ?string $TValue = null);

    /**
     * @param callable $callback (value:TValue,key:TKey):bool
     * @return IMap T: <TKey, TValue>
     */
    public function filter(callable $callback);

    /**
     * @param callable $reducer (total:<RValue>|<TValue>,value:<TValue>,key:<TKey>,map:Map):<RValue>|<TValue>
     * @param null|mixed $initialValue null|<RValue>
     * @return mixed <RValue>|<TValue>
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed;
}
