<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\IEnumerable;
use MF\Validator\TypeValidator;

/**
 * @phpstan-template T
 */
class PrioritizedCollection implements IEnumerable
{
    private const ALLOWED_TYPES = [
        TypeValidator::TYPE_ANY,
        TypeValidator::TYPE_MIXED,
        TypeValidator::TYPE_STRING,
        TypeValidator::TYPE_INT,
        TypeValidator::TYPE_FLOAT,
        TypeValidator::TYPE_BOOL,
        TypeValidator::TYPE_ARRAY,
        TypeValidator::TYPE_CALLABLE,
        TypeValidator::TYPE_OBJECT,
        TypeValidator::TYPE_INSTANCE_OF,
    ];

    /** @var TypeValidator */
    private $typeValidator;
    /**
     * @phpstan-var array<int, T>
     * @var array
     */
    private $items;
    /** @var array<int, int> */
    private $priorities;

    public function __construct(string $T)
    {
        $this->typeValidator = new TypeValidator(
            TypeValidator::TYPE_INT,
            $T,
            [TypeValidator::TYPE_INT],
            self::ALLOWED_TYPES,
            InvalidArgumentException::class
        );
        $this->items = [];
        $this->priorities = [];
    }

    /**
     * @phpstan-param T $item
     */
    public function add($item, int $priority): void
    {
        $this->typeValidator->assertValueType($item);
        $this->items[] = $item;
        $this->priorities[] = $priority;
    }

    /** @phpstan-return iterable<T> */
    public function getIterator(): iterable
    {
        yield from $this->getItemsByPriority();
    }

    /** @phpstan-return iterable<T> */
    private function getItemsByPriority(): iterable
    {
        $priorities = $this->priorities;
        usort(
            $priorities,
            function (int $a, int $b): int {
                return $b <=> $a;
            }
        );

        foreach (array_keys($priorities) as $index) {
            yield $this->items[$index];
        }
    }

    public function count(): int
    {
        return count($this->items);
    }
}
