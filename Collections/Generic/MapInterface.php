<?php

namespace MFCollections\Collections\Generic;

interface MapInterface extends \MFCollections\Collections\MapInterface
{
    /**
     * @param callable (key:<TKey>,value:<TValue>):<TValue> $callback
     * @param string|null $mappedMapKeyType
     * @param string|null $mappedMapValueType
     * @return \MFCollections\Collections\MapInterface|static
     */
    public function map($callback, $mappedMapKeyType = null, $mappedMapValueType = null);
}
