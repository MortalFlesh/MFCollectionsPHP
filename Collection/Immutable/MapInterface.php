<?php

namespace MF\Collection\Immutable;

interface MapInterface extends \MF\Collection\MapInterface, CollectionInterface
{
    /**
     * @param mixed $key
     * @param mixed $value
     * @return static
     */
    public function set($key, $value);

    /**
     * @param mixed $key
     * @return static
     */
    public function remove($key);

    /** @return ListInterface */
    public function keys();

    /** @return ListInterface */
    public function values();

    /** @return \MF\Collection\MapInterface */
    public function asMutable();
}
