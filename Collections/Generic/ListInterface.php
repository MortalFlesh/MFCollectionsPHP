<?php

namespace MF\Collections\Generic;

interface ListInterface extends \MF\Collections\ListInterface
{
    /**
     * @param callable (value:<TValue>,index:<TKey>):<TValue> $callback
     * @param string|null $mappedListValueType
     * @return \MF\Collections\ListInterface|static
     */
    public function map($callback, $mappedListValueType = null);
}
