<?php

namespace MFCollections\Collections\Immutable;

use MFCollections\Collections\CollectionInterface;

interface ListInterface extends CollectionInterface
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

    /** @return \MFCollections\Collections\ListInterface */
    public function asMutable();
}
