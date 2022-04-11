<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Immutable\Generic\ISeq;
use MF\Collection\Immutable\Generic\KVPair;
use MF\Collection\Immutable\ITuple;

/**
 * @phpstan-template TKey of int|string
 * @phpstan-template TValue
 *
 * @phpstan-extends ICollection<TKey, TValue>
 */
interface IMap extends ICollection, \ArrayAccess
{
    /**
     * @phpstan-param iterable<TKey, TValue> $source
     * @phpstan-return IMap<TKey, TValue>
     */
    public static function from(iterable $source): IMap;

    /**
     * @phpstan-param iterable<ITuple|KVPair<TKey, TValue>|array{0: TKey, 1: TValue}> $pairs
     * @phpstan-return IMap<TKey, TValue>
     */
    public static function fromPairs(iterable $pairs): IMap;

    /**
     * @phpstan-template T
     *
     * @phpstan-param iterable<TKey, T> $source
     * @phpstan-param callable(T, TKey): TValue $creator
     * @phpstan-return IMap<TKey, TValue>
     */
    public static function create(iterable $source, callable $creator): IMap;

    /** @phpstan-param TKey $key */
    public function containsKey(int|string $key): bool;

    /**
     * @phpstan-param TKey $key
     * @phpstan-return TValue|null
     */
    public function find(int|string $key): mixed;

    /**
     * @phpstan-param TValue $value
     * @phpstan-return TKey|null
     */
    public function findKey(mixed $value): mixed;

    /** @phpstan-return TValue */
    public function get(int|string $key): mixed;

    /**
     * @phpstan-param TKey $key
     * @phpstan-param TValue $value
     */
    public function set(int|string $key, mixed $value): void;

    /** @phpstan-param TKey $key */
    public function remove(int|string $key): void;

    public function clear(): void;

    /** @phpstan-return IList<TKey> */
    public function keys(): IList;

    /** @phpstan-return IList<TValue> */
    public function values(): IList;

    /** @phpstan-return IList<KVPair<TKey, TValue>> */
    public function pairs(): IList;

    /**
     * @phpstan-template T
     *
     * @phpstan-param callable(TValue, TKey): T $callback
     */
    public function map(callable $callback): void;

    /** @phpstan-param callable(TValue, TKey): bool $callback */
    public function filter(callable $callback): void;

    /**
     * @phpstan-template State
     *
     * @phpstan-param callable(State, TValue, TKey, IMap<TKey, TValue>): State $callback
     * @phpstan-param State $initialValue
     * @phpstan-return State
     */
    public function reduce(callable $callback, mixed $initialValue = null): mixed;

    /** @phpstan-return \MF\Collection\Immutable\Generic\IMap<TKey, TValue> */
    public function asImmutable(): \MF\Collection\Immutable\Generic\IMap;

    /** @phpstan-return IList<ITuple> */
    public function toList(): IList;

    /** @phpstan-return ISeq<ITuple> */
    public function toSeq(): ISeq;
}
