<?php declare(strict_types=1);

namespace MF\Collection\Fixtures;

use MF\Collection\Immutable\Generic\IList;
use MF\Collection\Immutable\Generic\ISeq;

class DummySeq implements ISeq
{
    public function __construct(private readonly iterable $source)
    {
    }

    public function forAll(callable $predicate): bool
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function count(): int
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function getIterator(): \Traversable
    {
        yield from $this->source;
    }

    public static function of(...$args): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public static function from(iterable $source): ISeq
    {
        return new self($source);
    }

    public static function create(iterable $iterable, callable $creator): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public static function createEmpty(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public static function range(array|string $range): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public static function infinite(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public static function forDo(array|string $range, callable $callable): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public static function init(iterable|callable $iterable): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public static function unfold(callable $callable, mixed $initialValue): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function toArray(): array
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function take(int $limit): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function takeWhile(callable $callable): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function takeUpTo(int $limit): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function filter(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function contains(mixed $value): bool
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function containsBy(callable $callback): bool
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function isEmpty(): bool
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function sort(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function sortDescending(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function sortBy(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function sortByDescending(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function unique(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function uniqueBy(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function reverse(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function sum(): int|float
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function sumBy(callable $callback): int|float
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function clear(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function append(ISeq $seq): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function chunkBySize(int $size): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function splitInto(int $count): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function map(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function each(callable $callback): void
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function collect(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function concat(): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function countBy(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function groupBy(callable $callback): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function min(): mixed
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function minBy(callable $callback): mixed
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function max(): mixed
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function maxBy(callable $callback): mixed
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function implode(string $glue): string
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function toList(): IList
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function skip(int $limit): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }

    public function skipWhile(callable $callable): ISeq
    {
        throw new \Exception(sprintf('Method %s is not implemented yet.', __METHOD__));
    }
}
