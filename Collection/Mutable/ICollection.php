<?php

namespace MF\Collection\Mutable;

interface ICollection extends \MF\Collection\ICollection
{
    /**
     * @param array $array
     * @return static
     */
    public static function createFromArray(array $array);

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

    /** @return \MF\Collection\Immutable\ICollection */
    public function asImmutable();
}
