<?php declare(strict_types=1);

namespace MF\Collection\Mutable;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /**
     * @param array $array
     * @param bool $recursive
     * @return IMap
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable|string $creator (value:mixed,key:mixed):mixed
     * @return IMap
     */
    public static function create(iterable $source, $creator);

    /**
     * @param callable|string $callback (key:mixed,value:mixed):bool
     */
    public function containsBy($callback): bool;

    /**
     * @param callable|string $callback (key:mixed,value:mixed):mixed
     * @return IMap
     */
    public function map($callback);

    /**
     * @param callable|string $callback (key:mixed,value:mixed):bool
     * @return IMap
     */
    public function filter($callback);

    /** @return \MF\Collection\Immutable\IMap */
    public function asImmutable();
}
