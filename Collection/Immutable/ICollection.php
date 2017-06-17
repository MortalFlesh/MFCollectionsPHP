<?php

namespace MF\Collection\Immutable;

interface ICollection extends \MF\Collection\ICollection
{
    /** @return static */
    public function clear();

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return static
     */
    public function map($callback);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return static
     */
    public function filter($callback);

    /** @return \MF\Collection\Mutable\ICollection */
    public function asMutable();
}
