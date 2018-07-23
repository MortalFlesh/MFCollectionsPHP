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
     * @param array $array
     * @param bool $recursive
     * @return IList
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable|string $creator (value:mixed,index:int):mixed
     * @return IList
     */
    public static function create(iterable $source, $creator);

    /** @return mixed */
    public function shift();

    /** @return mixed */
    public function pop();

    /**
     * @param callable|string $callback (value:mixed,index:mixed):mixed
     * @return IList
     */
    public function map($callback);

    /**
     * @param callable|string $callback (value:mixed,index:mixed):bool
     * @return IList
     */
    public function filter($callback);

    /** @return \MF\Collection\Immutable\IList */
    public function asImmutable();
}
