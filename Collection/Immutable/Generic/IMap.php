<?php

namespace MF\Collection\Immutable\Generic;

interface IMap extends \MF\Collection\Generic\IMap, \MF\Collection\Immutable\IMap
{
    /**
     * @param string $TKey
     * @param string $TValue
     * @param array $array <TKey, TValue>
     * @return IMap<TKey, TValue>
     */
    public static function fromKT(string $TKey, string $TValue, array $array);

    /**
     * @deprecated
     * @see IMap::fromKT()
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param <TKey> $key
     * @param <TValue> $value
     * @return IMap
     */
    public function set($key, $value);

    /**
     * @param <TKey> $key
     * @return IMap
     */
    public function remove($key);

    /** @return IMap */
    public function clear();

    /** @return IList<TKey> */
    public function keys();

    /** @return IList<TValue> */
    public function values();

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):<TValue>
     * @param string|null $TValue
     * @return IMap<TKey, TValue>
     */
    public function map($callback, $TValue = null);

    /**
     * @param callable $callback (value:TValue,index:TKey):bool
     * @return IMap<TKey, TValue>
     */
    public function filter($callback);

    /** @return \MF\Collection\Mutable\Generic\IMap */
    public function asMutable();
}
