<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\IEnumerable;
use MF\Collection\Immutable\ITuple;
use MF\Collection\Immutable\Tuple;
use MF\Validator\TypeValidator;

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
    /** @var ITuple[] (<TValue>, priority) */
    private $items;

    public function __construct(string $TValue)
    {
        $this->typeValidator = new TypeValidator(
            TypeValidator::TYPE_INT,
            $TValue,
            [TypeValidator::TYPE_INT],
            self::ALLOWED_TYPES,
            InvalidArgumentException::class
        );
        $this->items = [];
    }

    /** @param <TValue> $item */
    public function add($item, int $priority): void
    {
        $this->typeValidator->assertValueType($item);
        $this->items[] = Tuple::of($item, $priority);
    }

    /** @return <TValue>[] */
    public function getIterator(): iterable
    {
        yield from $this->getItemsByPriority();
    }

    /** @return <TValue>[] */
    private function getItemsByPriority(): iterable
    {
        $items = $this->items;
        usort(
            $items,
            function (Tuple $a, Tuple $b): int {
                return $b->second() <=> $a->second();
            }
        );

        foreach ($items as [$item]) {
            yield $item;
        }
    }

    public function count(): int
    {
        return count($this->items);
    }
}
