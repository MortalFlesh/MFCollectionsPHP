<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

interface IMap extends \MF\Collection\Generic\IMap, \MF\Collection\Immutable\IMap
{
    /**
     * @param array $array <TKey, TValue>
     * @return IMap<TKey, TValue>
     */
    public static function fromKT(string $TKey, string $TValue, array $array);

    /**
     * @param callable $creator (value:mixed,key:TKey):TValue
     * @return IMap<TKey, TValue>
     */
    public static function createKT(string $TKey, string $TValue, iterable $source, callable $creator);

    /**
     * @deprecated
     * @see IMap::fromKT()
     */
    public static function from(array $array, bool $recursive = false): self;

    /**
     * @deprecated
     * @see IMap::createKT()
     */
    public static function create(iterable $source, callable $creator);

    /**
     * @param <TKey> $key
     * @param <TValue> $value
     * @return IMap
     */
    public function set($key, $value);

    /**
     * @param callable $callback (value:TValue,key:TKey):bool
     */
    public function containsBy(callable $callback): bool;

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
    public function map(callable $callback, $TValue = null);

    /**
     * @param callable $callback (value:TValue,key:TKey):bool
     * @return IMap<TKey, TValue>
     */
    public function filter(callable $callback);

    /** @return \MF\Collection\Mutable\Generic\IMap */
    public function asMutable();
}
