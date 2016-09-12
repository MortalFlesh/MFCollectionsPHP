<?php

namespace MF\Collection\Generic;

interface MapInterface extends \MF\Collection\MapInterface, CollectionInterface
{
    /**
     * @param callable (key:<TKey>,value:<TValue>):<TValue> $callback
     * @param string|null $mappedMapValueType
     * @return \MF\Collection\Mutable\MapInterface|static
     */
    public function map($callback, $mappedMapValueType = null);
}
