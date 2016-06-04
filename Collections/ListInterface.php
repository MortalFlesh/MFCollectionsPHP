<?php

namespace MFCollections\Collections;

interface ListInterface extends CollectionInterface, \IteratorAggregate, \Countable
{
    /** @param mixed $value */
    public function add($value);

    /** @param mixed $value */
    public function unshift($value);

    /** @return mixed */
    public function shift();

    /** @return mixed */
    public function pop();

    /** @return mixed */
    public function first();

    /** @return mixed */
    public function last();

    /** @return static */
    public function sort();

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value);

    /** @param mixed $value */
    public function removeFirst($value);

    /** @param mixed $value */
    public function removeAll($value);
}
