<?php

namespace MF\Collection\Immutable;

interface ICollection extends \MF\Collection\ICollection
{
    /**
     * @param array $array
     * @param bool $recursive
     * @return ICollection
     */
    public static function from(array $array, bool $recursive = false);

    /** @return ICollection */
    public function clear();

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return ICollection
     */
    public function map($callback);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return ICollection
     */
    public function filter($callback);

    /** @return \MF\Collection\Mutable\ICollection */
    public function asMutable();
}
