<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

interface ICollection extends \MF\Collection\ICollection
{
    /**
     * @return ICollection
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @param callable $creator (value:mixed,index:mixed):mixed
     * @return ICollection
     */
    public static function create(iterable $source, callable $creator);

    /** @return ICollection */
    public function clear();

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

    /** @return \MF\Collection\Mutable\ICollection */
    public function asMutable();
}
