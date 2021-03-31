<?php declare(strict_types=1);

namespace MF\Collection;

interface IList extends ICollection
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
     * @return void
     */
    public function add(mixed $value);

    /**
     * @return void
     */
    public function unshift(mixed $value);

    public function first(): mixed;

    /**
     * @param callable $callback (value:mixed,index:int):bool
     */
    public function firstBy(callable $callback): mixed;

    public function last(): mixed;

    /** @return IList */
    public function sort();

    /**
     * @return void
     */
    public function removeFirst(mixed $value);

    /**
     * @return void
     */
    public function removeAll(mixed $value);

    /**
     * @param callable $callback (value:mixed,index:int):mixed
     * @return IList
     */
    public function map(callable $callback);

    /**
     * @param callable $callback (value:mixed,index:int):bool
     * @return IList
     */
    public function filter(callable $callback);

    public function implode(string $glue): string;
}
