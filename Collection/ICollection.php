<?php

namespace MF\Collection;

interface ICollection extends \IteratorAggregate, \Countable
{
    /**
     * @param array $array
     * @return static
     */
    public static function createFromArray(array $array);

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value): bool;

    public function clear();

    public function isEmpty(): bool;

    public function toArray(): array;

    /** @param callable (value:mixed,index:mixed):void $callback */
    public function each(callable $callback): void;

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
