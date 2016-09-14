<?php

namespace MF\Collection\Immutable;

interface IMap extends \MF\Collection\IMap, ICollection
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

    /** @return IList */
    public function keys();

    /** @return IList */
    public function values();

    /** @return \MF\Collection\IMap */
    public function asMutable();
}
