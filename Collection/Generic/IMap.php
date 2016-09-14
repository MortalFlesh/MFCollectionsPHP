<?php

namespace MF\Collection\Generic;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /**
     * @param callable (key:<TKey>,value:<TValue>):<TValue> $callback
     * @param string|null $mappedMapValueType
     * @return \MF\Collection\Mutable\IMap|static
     */
    public function map($callback, $mappedMapValueType = null);
}
