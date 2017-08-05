<?php

namespace MF\Collection\Mutable\Generic;

interface IList extends \MF\Collection\Generic\IList, \MF\Collection\Mutable\IList
{
    /**
     * @param string $TValue
     * @param array $array <TValue>
     * @return IList<TValue>
     */
    public static function ofT(string $TValue, array $array);

    /**
     * @deprecated
     * @see IList::ofT()
     */
    public static function of(array $array, bool $recursive = false);

    /**
     * @param callable $callback (value:<TValue>,index:int):<TValue>
     * @param string|null $TValue
     * @return IList<TValue>
     */
    public function map($callback, string $TValue = null);

    /**
     * @param callable $callback (value:<TValue>,index:int):bool
     * @return IList<TValue>
     */
    public function filter($callback);

    /** @return \MF\Collection\Immutable\Generic\IList */
    public function asImmutable();
}
