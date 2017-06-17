<?php

namespace MF\Collection\Generic;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):<TValue>
     * @param string|null $mappedMapValueType
     * @return \MF\Collection\Mutable\IMap|static
     */
    public function map($callback, $mappedMapValueType = null);
}
