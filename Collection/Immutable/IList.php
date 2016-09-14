<?php

namespace MF\Collection\Immutable;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @param mixed $value
     * @return static
     */
    public function add($value);

    /**
     * @param mixed $value
     * @return static
     */
    public function unshift($value);

    /**
     * @param mixed $value
     * @return static
     */
    public function removeFirst($value);

    /**
     * @param mixed $value
     * @return static
     */
    public function removeAll($value);

    /** @return \MF\Collection\IList */
    public function asMutable();
}
