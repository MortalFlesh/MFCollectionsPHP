<?php

namespace MF\Collection;

interface IMap extends ICollection, \ArrayAccess
{
    /**
     * @param mixed $key
     * @return bool
     */
    public function containsKey($key);

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

    /** @return IList */
    public function keys();

    /** @return IList */
    public function values();
}
