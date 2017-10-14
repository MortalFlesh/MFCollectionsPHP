<?php

namespace MF\Collection\Generic;

interface IList extends \MF\Collection\IList, ICollection
{
    /**
     * @param string $TValue
     * @param array $array <TValue>
     * @return IList<TValue>
     */
    public static function fromT(string $TValue, array $array);

    /**
     * @deprecated
     * @see IList::fromT()
     */
    public static function from(array $array, bool $recursive = false);

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

    /**
     * @param callable $reducer (total:<RValue>|<TValue>,value:<TValue>,index:int,list:IList):<RValue>|<TValue>
     * @param null|<RValue> $initialValue
     * @return <RValue>|<TValue>
     */
    public function reduce($reducer, $initialValue = null);
}
