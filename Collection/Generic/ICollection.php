<?php

namespace MF\Collection\Generic;

interface ICollection extends \MF\Collection\ICollection
{
    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericFromArray(string $keyType, string $valueType, array $array);

    /**
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericListFromArray(string $valueType, array $array);

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     * @return static
     */
    public function map($callback);

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     * @return static
     */
    public function filter($callback);
}
