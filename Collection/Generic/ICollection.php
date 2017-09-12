<?php

namespace MF\Collection\Generic;

interface ICollection extends \MF\Collection\ICollection
{
    const INDEX_TVALUE = 2;

    /**
     * @deprecated
     * @see IList::ofT()
     * @see IMap::ofKT()
     */
    public static function of(array $array, bool $recursive = false);
}
