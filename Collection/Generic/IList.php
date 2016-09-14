<?php

namespace MF\Collection\Generic;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @param callable (value:<TValue>,index:<TKey>):<TValue> $callback
     * @param string|null $mappedListValueType
     * @return \MF\Collection\Mutable\IList|static
     */
    public function map($callback, $mappedListValueType = null);
}
