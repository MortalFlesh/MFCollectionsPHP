<?php declare(strict_types=1);

namespace MF\Collection\Mutable;

interface ICollection extends \MF\Collection\ICollection
{
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
     * @param callable|string $callback (value:mixed,index:mixed):mixed
     * @return ICollection
     */
    public function map($callback);

    /**
     * @param callable|string $callback (value:mixed,index:mixed):bool
     * @return ICollection
     */
    public function filter($callback);

    /** @return \MF\Collection\Immutable\ICollection */
    public function asImmutable();
}
