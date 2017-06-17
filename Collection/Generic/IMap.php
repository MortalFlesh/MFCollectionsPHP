<?php

namespace MF\Collection\Generic;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function ofKT(string $keyType, string $valueType, array $array);

    /**
     * @param callable $callback (key:<TKey>,value:<TValue>):<TValue>
     * @param string|null $mappedMapValueType
     * @return \MF\Collection\Mutable\IMap|static
     */
    public function map($callback, $mappedMapValueType = null);
}
