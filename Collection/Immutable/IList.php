<?php

namespace MF\Collection\Immutable;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @param mixed $value
     * @return IList
     */
    public function add($value);

    /**
     * @param mixed $value
     * @return IList
     */
    public function unshift($value);

    /**
     * @param mixed $value
     * @return IList
     */
    public function removeFirst($value);

    /**
     * @param mixed $value
     * @return IList
     */
    public function removeAll($value);

    /** @return IList */
    public function clear();

    /** @return IList */
    public function sort();

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return IList
     */
    public function map($callback);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return IList
     */
    public function filter($callback);

    /** @return \MF\Collection\IList */
    public function asMutable();
}
