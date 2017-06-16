<?php

namespace MF\Collection\Immutable;

interface ICollection extends \MF\Collection\ICollection
{
    /**
     * @param array $array
     * @return static
     */
    public static function createFromArray(array $array);

    /** @return static */
    public function clear();

    /**
     * @param callable (value:mixed,index:mixed):mixed $callback
     * @return static
     */
    public function map($callback);

    /**
     * @param callable (value:mixed,index:mixed):bool $callback
     * @return static
     */
    public function filter($callback);

    /** @return \MF\Collection\Mutable\ICollection */
    public function asMutable();
}
