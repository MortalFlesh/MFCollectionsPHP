<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Generic\IEnumerable;
use MF\Collection\Immutable\ITuple;
use MF\Collection\Immutable\Tuple;

/**
 * @phpstan-type TIndex int
 * @phpstan-template TValue
 *
 * @phpstan-implements IEnumerable<TIndex, TValue>
 */
class PrioritizedCollection implements IEnumerable
{
    /** @var ITuple[] (<TValue>, priority) */
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    /** @phpstan-param TValue $item */
    public function add(mixed $item, int $priority): void
    {
        $this->items[] = Tuple::of($item, $priority);
    }

    /** @phpstan-return iterable<TIndex, TValue> */
    private function getItemsByPriority(): iterable
    {
        $items = $this->items;
        usort(
            $items,
            fn (Tuple $a, Tuple $b) => $b->second() <=> $a->second()
        );

        foreach ($items as [$item]) {
            yield $item;
        }
    }

    public function count(): int
    {
        return count($this->items);
    }

    /** @phpstan-return \Traversable<TIndex, TValue> */
    public function getIterator(): \Traversable
    {
        yield from $this->getItemsByPriority();
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
