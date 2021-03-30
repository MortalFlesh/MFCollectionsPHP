<?php declare(strict_types=1);

namespace MF\Collection;

interface IList extends ICollection
{
    /**
     * @param mixed $values
     * @return IList
     */
    public static function of(...$values);

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
     * @param mixed $value
     * @return void
     */
    public function add($value);

    /**
     * @param mixed $value
     * @return void
     */
    public function unshift($value);

    /** @return mixed */
    public function first();

    /**
     * @param callable $callback (value:mixed,index:int):bool
     * @return mixed
     */
    public function firstBy(callable $callback);

    /** @return mixed */
    public function last();

    /** @return IList */
    public function sort();

    /**
     * @param mixed $value
     * @return void
     */
    public function removeFirst($value);

    /**
     * @param mixed $value
     * @return void
     */
    public function removeAll($value);

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
