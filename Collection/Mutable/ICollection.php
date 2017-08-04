<?php

namespace MF\Collection\Mutable;

interface ICollection extends \MF\Collection\ICollection
{
    /**
     * @param array $array
     * @param bool $recursive
     * @return ICollection
     */
    public static function of(array $array, bool $recursive = false);

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

    /** @return \MF\Collection\Immutable\ICollection */
    public function asImmutable();
}
