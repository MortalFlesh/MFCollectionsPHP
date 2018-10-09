<?php declare(strict_types=1);

namespace MF\Collection;

interface ICollection extends IEnumerable
{
    const MAP = 'map';
    const FILTER = 'filter';

    /**
     * @return ICollection
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable|string $creator (value:mixed,index:mixed):mixed
     * @return ICollection
     */
    public static function create(iterable $source, $creator);

    /**
     * @param mixed $value
     */
    public function contains($value): bool;

    /**
     * @param callable|string $callback (value:mixed,index:mixed):bool
     */
    public function containsBy($callback): bool;

    public function clear();

    public function isEmpty(): bool;

    public function toArray(): array;

    /** @param callable $callback (value:mixed,index:mixed):void */
    public function each(callable $callback): void;

    /**
     * @param callable|string $callback (value:mixed,index:mixed):mixed
     * @return ICollection
     */
    public function map($callback);

    /**
     * @param callable|string $callback (value:mixed,index:mixed):bool
     * @return ICollection
     */
    public function filter($callback);

    /**
     * @param callable|string $reducer (total:mixed,value:mixed,index:mixed,collection:ICollection):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null);
}
