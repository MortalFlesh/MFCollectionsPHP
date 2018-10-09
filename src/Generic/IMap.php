<?php declare(strict_types=1);

namespace MF\Collection\Generic;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /**
     * @param array $array <TKey, TValue>
     * @return IMap<TKey, TValue>
     */
    public static function fromKT(string $TKey, string $TValue, array $array);

    /**
     * @param callable|string $creator (value:mixed,key:TKey):TValue
     * @return IMap<TKey, TValue>
     */
    public static function createKT(string $TKey, string $TValue, iterable $source, $creator);

    /**
     * @deprecated
     * @see IMap::fromKT()
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @deprecated
     * @see IMap::createKT()
     * @param mixed $creator
     */
    public static function create(iterable $source, $creator);

    /** @return IList<TKey> */
    public function keys();

    /** @return IList<TValue> */
    public function values();

    /**
     * @param callable|string $callback (value:TValue,key:TKey):bool
     */
    public function containsBy($callback): bool;

    /**
     * @param callable|string $callback (key:<TKey>,value:<TValue>):<TValue>
     * @param string|null $TValue
     * @return IMap<TKey, TValue>
     */
    public function map($callback, $TValue = null);

    /**
     * @param callable|string $callback (value:TValue,key:TKey):bool
     * @return IMap<TKey, TValue>
     */
    public function filter($callback);

    /**
     * @param callable|string $reducer (total:<RValue>|<TValue>,value:<TValue>,key:<TKey>,map:Map):<RValue>|<TValue>
     * @param null|<RValue> $initialValue
     * @return <RValue>|<TValue>
     */
    public function reduce($reducer, $initialValue = null);
}
