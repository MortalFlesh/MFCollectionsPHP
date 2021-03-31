<?php declare(strict_types=1);

namespace MF\Collection;

interface IMap extends ICollection, \ArrayAccess
{
    /**
     * @return IMap
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable $creator (value:mixed,key:mixed):mixed
     * @return IMap
     */
    public static function create(iterable $source, callable $creator);

    public function containsKey(mixed $key): bool;

    public function find(mixed $value): mixed;

    public function get(mixed $key): mixed;

    /**
     * @return void
     */
    public function set(mixed $key, mixed $value);

    /**
     * @param callable $callback (value:mixed,key:mixed):bool
     */
    public function containsBy(callable $callback): bool;

    /**
     * @return void
     */
    public function remove(mixed $key);

    /** @return IList */
    public function keys();

    /** @return IList */
    public function values();

    /**
     * @param callable $callback (value:mixed,key:mixed):mixed
     * @return IMap
     */
    public function map(callable $callback);

    /**
     * @param callable $callback (value:mixed,key:mixed):bool
     * @return IMap
     */
    public function filter(callable $callback);
}
