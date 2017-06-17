<?php

namespace MF\Collection\Generic;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @param callable $callback (value:<TValue>,index:<TKey>):<TValue>
     * @param string|null $mappedListValueType
     * @return \MF\Collection\Mutable\IList|static
     */
    public function map($callback, $mappedListValueType = null);
}
