<?php

namespace MF\Collection;

interface IList extends ICollection
{
    /**
     * @param mixed $values
     * @return IList
     */
    public static function of(...$values);

    /**
     * @param array $array
     * @param bool $recursive
     * @return IList
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable $creator (value:mixed,index:int):mixed
     * @return IList
     */
    public static function create(iterable $source, $creator);

    /** @param mixed $value */
    public function add($value);

    /** @param mixed $value */
    public function unshift($value);

    /** @return mixed */
    public function first();

    /** @return mixed */
    public function last();

    /** @return IList */
    public function sort();

    /** @param mixed $value */
    public function removeFirst($value);

    /** @param mixed $value */
    public function removeAll($value);

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return IList
     */
    public function map($callback);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return IList
     */
    public function filter($callback);

    public function implode(string $glue): string;
}
