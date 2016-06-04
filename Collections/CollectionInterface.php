<?php

namespace MFCollections\Collections;

interface CollectionInterface
{
    /**
     * @param array $array
     * @return static
     */
    public static function createFromArray(array $array);

    /** @return array */
    public function toArray();

    /** @param callable $callback */
    public function each($callback);

    /**
     * @param callable $callback
     * @return static
     */
    public function map($callback);

    /**
     * @param callable $callback
     * @return static
     */
    public function filter($callback);
}
