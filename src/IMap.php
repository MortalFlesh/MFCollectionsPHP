<?php declare(strict_types=1);

namespace MF\Collection;

interface IMap extends ICollection, \ArrayAccess
{
    /**
     * @param array $array
     * @param bool $recursive
     * @return IMap
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable $creator (value:mixed,key:mixed):mixed
     * @return IMap
     */
    public static function create(iterable $source, $creator);

    /**
     * @param mixed $key
     * @return bool
     */
    public function containsKey($key): bool;

    /**
     * @param mixed $value
     * @return mixed|false
     */
    public function find($value);

    /**
     * @param mixed $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value);

    /** @param mixed $key */
    public function remove($key);

    /** @return IList */
    public function keys();

    /** @return IList */
    public function values();

    /**
     * @param callable $callback (value:mixed,key:mixed):mixed
     * @return IMap
     */
    public function map($callback);

    /**
     * @param callable $callback (value:mixed,key:mixed):bool
     * @return IMap
     */
    public function filter($callback);
}
