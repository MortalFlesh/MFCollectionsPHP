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

    private TypeValidator $typeValidator;
    /** @var ITuple[] (<TValue>, priority) */
    private array $items;

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

    /** @param mixed $item T: <TValue> */
    public function add(mixed $item, int $priority): void
    {
        $this->typeValidator->assertValueType($item);
        $this->items[] = Tuple::of($item, $priority);
    }

    /** @return \Traversable T: <TValue>[] */
    public function getIterator(): \Traversable
    {
        yield from $this->getItemsByPriority();
    }

    /** @return iterable T: <TValue>[] */
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
}
