<?php

namespace MF\Collection\Generic;

interface CollectionInterface extends \MF\Collection\CollectionInterface
{
    /**
     * @param string $keyType
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericFromArray($keyType, $valueType, array $array);

    /**
     * @param string $valueType
     * @param array $array
     * @return static
     */
    public static function createGenericListFromArray($valueType, array $array);
}
