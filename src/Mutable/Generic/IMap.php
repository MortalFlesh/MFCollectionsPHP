<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

/**
 * @template TKey
 * @template TValue
 */
interface IMap extends \MF\Collection\Generic\IMap, \MF\Collection\Mutable\IMap
{
    /**
     * @param array<TKey, TValue> $array
     * @return IMap<TKey, TValue>
     */
    public static function fromKT(string $TKey, string $TValue, array $array): self;

    /**
     * @param callable(mixed, TKey): TValue $creator
     * @return IMap<TKey, TValue>
     */
    public static function createKT(string $TKey, string $TValue, iterable $source, callable $creator): self;

    /**
     * @deprecated
     * @see IMap::fromKT()
     */
    public static function from(array $array, bool $recursive = false): self;

    /**
     * @deprecated
     * @see IMap::createKT()
     */
    public static function create(iterable $source, callable $creator): self;

    /** @return IList<TKey> */
    public function keys(): IList;

    /** @return IList<TValue> */
    public function values(): IList;

    /**
     * @param callable(TValue, TKey): bool $callback
     */
    public function containsBy(callable $callback): bool;

    /**
     * @param callable(TKey, TValue): TValue $callback
     * @param string|null $TValue
     * @return IMap<TKey, TValue>
     */
    public function map(callable $callback, $TValue = null): self;

    /**
     * @param callable(TValue, TKey): bool $callback (value:TValue,key:TKey):bool
     * @return IMap<TKey, TValue>
     */
    public function filter(callable $callback): self;

    public function asImmutable(): \MF\Collection\Immutable\Generic\IMap;
}
