<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

interface IMap extends \MF\Collection\IMap, ICollection
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

    /**
     * @return IMap
     */
    public function set(mixed $key, mixed $value);

    /**
     * @return IMap
     */
    public function remove(mixed $key);

    /** @return IMap */
    public function clear();

    /** @return IList */
    public function keys();

    /** @return IList */
    public function values();

    /**
     * @param callable $callback (value:mixed,key:mixed):mixed
     * @return IMap
     */
    public function map(callable $callback);

    /** @return \MF\Collection\Mutable\IMap */
    public function asMutable();
}
