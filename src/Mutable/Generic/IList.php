<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

/**
 * @template U
 * @template TValue
 */
interface IList extends \MF\Collection\Generic\IList, \MF\Collection\Mutable\IList
{
    /**
     * @param <TValue> $values
     * @return IList<TValue>
     */
    public static function ofT(string $TValue, ...$values): self;

    /**
     * @param array<TValue> $array
     * @return IList<TValue>
     */
    public static function fromT(string $TValue, array $array): self;

    /**
     * @param iterable<U> $source
     * @param callable(U, int): TValue $creator
     * @return IList<TValue>
     */
    public static function createT(string $TValue, iterable $source, callable $creator): self;

    /**
     * @deprecated
     * @see IList::ofT()
     */
    public static function of(...$values): self;

    /**
     * @deprecated
     * @see IList::fromT()
     */
    public static function from(array $array, bool $recursive = false): self;

    /**
     * @deprecated
     * @see IList::createT()
     */
    public static function create(iterable $source, callable $creator): self;

    /**
     * @param callable(TValue, int): bool $callback
     */
    public function containsBy(callable $callback): bool;

    /**
     * @template TMappedValue
     * @param callable(TValue, int): TMappedValue $callback
     * @return IList<TMappedValue>
     */
    public function map(callable $callback, string $TValue = null): self;

    /**
     * @param callable(TValue, int): bool $callback
     * @return IList<TValue>
     */
    public function filter(callable $callback): self;

    public function asImmutable(): \MF\Collection\Immutable\Generic\IList;
}
