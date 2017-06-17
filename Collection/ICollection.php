<?php

namespace MF\Collection;

interface ICollection extends \IteratorAggregate, \Countable
{
    /**
     * @param array $array
     * @return ICollection
     */
    public static function of(array $array);

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value): bool;

    public function clear();

    public function isEmpty(): bool;

    public function toArray(): array;

    /** @param callable $callback (value:mixed,index:mixed):void */
    public function each(callable $callback): void;

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

    /**
     * @param callable $reducer (total:mixed,value:mixed,index:mixed,collection:ICollection):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null);
}
