<?php

namespace MF\Collection\Generic;

interface ICollection extends \MF\Collection\ICollection
{
    const INDEX_TVALUE = 2;

    /**
     * @deprecated
     * @see IList::fromT()
     * @see IMap::fromKT()
     */
    public static function from(array $array, bool $recursive = false);

    /**
     * @deprecated
     * @see IList::createT()
     * @see IMap::createKT()
     */
    public static function create(iterable $source, $creator);
}
