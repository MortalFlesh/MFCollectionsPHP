<?php declare(strict_types=1);

namespace MF\Collection\Mutable;

interface IList extends \MF\Collection\IList, ICollection
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

    /** @return mixed */
    public function shift();

    /** @return mixed */
    public function pop();

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

    /** @return \MF\Collection\Immutable\IList */
    public function asImmutable();
}
