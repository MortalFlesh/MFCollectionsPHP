<?php

namespace MF\Collection\Generic;

interface ListInterface extends \MF\Collection\ListInterface, CollectionInterface
{
    /**
     * @param callable (value:<TValue>,index:<TKey>):<TValue> $callback
     * @param string|null $mappedListValueType
     * @return \MF\Collection\Mutable\ListInterface|static
     */
    public function map($callback, $mappedListValueType = null);
}
