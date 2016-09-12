<?php

namespace MF\Collection;

interface MapInterface extends CollectionInterface, \ArrayAccess
{
    /**
     * @param mixed $key
     * @return bool
     */
    public function containsKey($key);

    /**
     * @param mixed $value
     * @return bool
     */
    public function contains($value);

    /**
     * @param mixed $value
     * @return mixed|false
     */
    public function find($value);

    /**
     * @param mixed $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value);

    /** @param mixed $key */
    public function remove($key);

    /** @return ListInterface */
    public function keys();

    /** @return ListInterface */
    public function values();
}
