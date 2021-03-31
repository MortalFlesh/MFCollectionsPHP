<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @return IList
     */
    public static function of(mixed ...$values);

    /**
     * @return IList
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable $creator (value:mixed,index:int):mixed
     * @return IList
     */
    public static function create(iterable $source, callable $creator);

    /**
     * @return IList
     */
    public function add(mixed $value);

    /**
     * @return IList
     */
    public function unshift(mixed $value);

    /**
     * @return IList
     */
    public function removeFirst(mixed $value);

    /**
     * @return IList
     */
    public function removeAll(mixed $value);

    /** @return IList */
    public function clear();

    /** @return IList */
    public function sort();

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return IList
     */
    public function map(callable $callback);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return IList
     */
    public function filter(callable $callback);

    /** @return \MF\Collection\IList */
    public function asMutable();
}
