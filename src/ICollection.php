<?php declare(strict_types=1);

namespace MF\Collection;

interface ICollection extends IEnumerable
{
    public const MAP = 'map';
    public const FILTER = 'filter';

    /**
     * @return ICollection
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable $creator (value:mixed,index:mixed):mixed
     * @return ICollection
     */
    public static function create(iterable $source, callable $creator);

    public function contains(mixed $value): bool;

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     */
    public function containsBy(callable $callback): bool;

    /** @return void */
    public function clear();

    public function isEmpty(): bool;

    public function toArray(): array;

    /** @param callable $callback (value:mixed,index:mixed):void */
    public function each(callable $callback): void;

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return ICollection
     */
    public function map(callable $callback);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return ICollection
     */
    public function filter(callable $callback);

    /**
     * @param callable $reducer (total:mixed,value:mixed,index:mixed,collection:ICollection):mixed
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed;
}
