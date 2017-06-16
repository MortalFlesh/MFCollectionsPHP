<?php

namespace MF\Collection\Generic;

interface ICollection extends \MF\Collection\ICollection
{
    /**
     * @param array $array
     * @return static
     */
    public static function createFromArray(array $array);

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
     * @param callable (value:mixed,index:mixed):mixed $callback
     * @return static
     */
    public function map($callback);

    /**
     * @param callable (value:mixed,index:mixed):bool $callback
     * @return static
     */
    public function filter($callback);
}
