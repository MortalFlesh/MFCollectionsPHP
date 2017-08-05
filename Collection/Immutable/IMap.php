<?php

namespace MF\Collection\Immutable;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /**
     * @param array $array
     * @param bool $recursive
     * @return IMap
     */
    public static function of(array $array, bool $recursive = false);

    /**
     * @param mixed $key
     * @param mixed $value
     * @return IMap
     */
    public function set($key, $value);

    /**
     * @param mixed $key
     * @return IMap
     */
    public function remove($key);

    /** @return IMap */
    public function clear();

    /** @return IList */
    public function keys();

    /** @return IList */
    public function values();

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return IMap
     */
    public function map($callback);

    /** @return \MF\Collection\Mutable\IMap */
    public function asMutable();
}
