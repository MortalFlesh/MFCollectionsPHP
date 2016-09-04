<?php

namespace MF\Collections;

interface CollectionInterface extends \IteratorAggregate, \Countable
{
    /**
     * @param array $array
     * @return static
     */
    public static function createFromArray(array $array);

    /** @return array */
    public function toArray();

    /** @param callable (value:mixed,index:mixed):void $callback */
    public function each($callback);

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

    /**
     * @param callable (total:mixed,value:mixed,index:mixed,collection:CollectionInterface):mixed $reducer
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null);
}
