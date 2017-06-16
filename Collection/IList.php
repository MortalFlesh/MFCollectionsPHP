<?php

namespace MF\Collection;

interface IList extends ICollection
{
    /** @param mixed $value */
    public function add($value);

    /** @param mixed $value */
    public function unshift($value);

    /** @return mixed */
    public function first();

    /** @return mixed */
    public function last();

    /** @return static */
    public function sort();

    /** @param mixed $value */
    public function removeFirst($value);

    /** @param mixed $value */
    public function removeAll($value);
}