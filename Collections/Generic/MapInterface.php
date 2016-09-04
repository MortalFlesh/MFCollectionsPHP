<?php

namespace MF\Collections\Generic;

interface MapInterface extends \MF\Collections\MapInterface
{
    /**
     * @param callable (key:<TKey>,value:<TValue>):<TValue> $callback
     * @param string|null $mappedMapKeyType
     * @param string|null $mappedMapValueType
     * @return \MF\Collections\MapInterface|static
     */
    public function map($callback, $mappedMapKeyType = null, $mappedMapValueType = null);
}
