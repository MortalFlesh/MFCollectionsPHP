<?php declare(strict_types=1);

namespace MF\Collection;

interface IEnumerable extends \IteratorAggregate, \Countable
{
    public function count(): int;

    public function getIterator(): iterable;
}
