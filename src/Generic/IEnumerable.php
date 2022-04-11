<?php declare(strict_types=1);

namespace MF\Collection\Generic;

/**
 * @phpstan-template TKey of int|string
 * @phpstan-template TValue
 *
 * @phpstan-extends \IteratorAggregate<TKey, TValue>
 */
interface IEnumerable extends \IteratorAggregate, \Countable
{
    public function count(): int;

    /** @phpstan-return \Traversable<TKey, TValue> */
    public function getIterator(): \Traversable;

    public function isEmpty(): bool;
}
