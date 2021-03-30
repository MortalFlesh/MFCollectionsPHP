<?php declare(strict_types=1);

namespace MF\Collection\Mutable;

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
     * @param callable $callback (key:mixed,value:mixed):bool
     */
    public function containsBy(callable $callback): bool;

    /**
     * @param callable $callback (key:mixed,value:mixed):mixed
     * @return IMap
     */
    public function map(callable $callback);

    /**
     * @param callable $callback (key:mixed,value:mixed):bool
     * @return IMap
     */
    public function filter(callable $callback);

    /** @return \MF\Collection\Immutable\IMap */
    public function asImmutable();
}
