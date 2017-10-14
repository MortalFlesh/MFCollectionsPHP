<?php

namespace MF\Collection\Mutable\Generic;

interface IMap extends \MF\Collection\Generic\IMap, \MF\Collection\Mutable\IMap
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

    /** @return \MF\Collection\Immutable\Generic\IMap */
    public function asImmutable();
}
