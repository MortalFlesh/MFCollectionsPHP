<?php

namespace MF\Collection\Immutable;

interface ListInterface extends \MF\Collection\ListInterface, CollectionInterface
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

    /** @return \MF\Collection\ListInterface */
    public function asMutable();
}
