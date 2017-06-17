<?php

namespace MF\Collection\Mutable;

interface ICollection extends \MF\Collection\ICollection
{
    /*
     * todo
     * - projit ICollection
     *      - pokud nebude mozne returnovat konkretni instanci, zrusit override metody
     */

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

    /** @return \MF\Collection\Immutable\ICollection */
    public function asImmutable();
}
