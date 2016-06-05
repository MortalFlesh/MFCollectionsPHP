<?php

namespace MFCollections\Collections\Generic;

interface ListInterface extends \MFCollections\Collections\ListInterface
{
    /**
     * @param callable (value:<TValue>,index:<TKey>):<TValue> $callback
     * @param string|null $mappedListValueType
     * @return \MFCollections\Collections\ListInterface|static
     */
    public function map($callback, $mappedListValueType = null);
}
