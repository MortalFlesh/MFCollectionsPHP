<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use Assert\AssertionFailedException;
use MF\Collection\Assertion;
use MF\Collection\Exception\TupleBadMethodCallException;
use MF\Collection\Exception\TupleException;
use MF\Collection\Exception\TupleMatchException;
use MF\Collection\Exception\TupleParseException;
use MF\Collection\Immutable\Generic\Seq;

readonly class Tuple implements ITuple
{
    private const MINIMAL_TUPLE_ITEMS_COUNT = 2;

    public static function fst(ITuple $tuple): mixed
    {
        return $tuple->first();
    }

    public static function snd(ITuple $tuple): mixed
    {
        return $tuple->second();
    }

    /**
     * Parse "(x, y, ... z)" string into Tuple(x, y, z)
     *
     * @example
     * Tuple::parse('(foo, bar)')->toArray()        -> ['foo', 'bar']
     * Tuple::parse('("foo bar", boo)')->toArray()  -> ['foo bar', 'boo']
     * Tuple::parse('(1, 2, 3)')->toArray()         -> [1, 2, 3]
     * Tuple::parse('(1, 2, 3)', 3)->toArray()      -> [1, 2, 3]     // with expectation
     *
     * Invalid (throws an \InvalidArgumentException):
     * Tuple::parse('(1, 2, 3)', 2)  // 2 values expected, but got 3
     * Tuple::parse('(1, 2)', 3)     // 3 values expected, but got 2
     *
     * @throws TupleParseException
     */
    public static function parse(string $tuple, int $expectedItemsCount = null): ITuple
    {
        try {
            return self::parseTuple($tuple, $expectedItemsCount);
        } catch (AssertionFailedException $e) {
            throw TupleParseException::forFailedAssertion($e);
        }
    }

    private static function parseTuple(string $tuple, ?int $expectedItemsCount): self
    {
        if (empty($tuple)) {
            return new self([]);
        }

        if ($expectedItemsCount !== null) {
            Assertion::greaterOrEqualThan(
                $expectedItemsCount,
                self::MINIMAL_TUPLE_ITEMS_COUNT,
                'Expected items count is %d but must not be lower than %d because in that case it would not be a valid Tuple.',
            );
        }

        $cache = [];

        /** @var string[] $parts */
        $parts = Seq::init(fn () => explode(',', trim($tuple, '()')))
            ->reduce(function (array $matches, string $match) use (&$cache) {
                $trimmedMatch = ltrim($match);
                $isStart = (str_starts_with($trimmedMatch, '"') || str_starts_with($trimmedMatch, "'"))
                    && empty($cache);
                $isEnd = (str_ends_with($match, '"') || str_ends_with($match, "'"))
                    && (mb_strlen($trimmedMatch) > 1 || !empty($cache));

                if (!$isEnd && ($isStart || !empty($cache))) {
                    $cache[] = $match;

                    return $matches;
                } elseif ($isEnd && !empty($cache)) {
                    $cache[] = $match;

                    $match = implode(',', $cache);
                    $cache = [];
                }

                $matches[] = $match;

                return $matches;
            }, []);
        unset($cache);

        $values = Seq::from($parts)
            ->map(fn (string $value) => trim($value))
            ->filter(fn ($match) => $match !== '')
            ->map(self::mapParsedItem(...))
            ->toArray();

        if ($expectedItemsCount !== null) {
            Assertion::count(
                $values,
                $expectedItemsCount,
                'Invalid tuple given - expected %d items but parsed %d items from "' . $tuple . '".',
            );
        }

        return new self($values);
    }

    private static function mapParsedItem(string $item): mixed
    {
        $item = trim($item);
        if (is_numeric($item) && mb_strpos($item, '.') === false) {
            return (int) $item;
        } elseif (is_numeric($item)) {
            return (float) $item;
        } elseif ($item === 'true') {
            return true;
        } elseif ($item === 'false') {
            return false;
        } elseif ($item === 'null') {
            return null;
        } elseif (str_starts_with($item, '[') && str_ends_with($item, ']')) {
            $arrayContent = trim($item, '[]');
            Assertion::false(
                str_contains($arrayContent, '[') || str_contains($arrayContent, ']'),
                sprintf('Tuple must NOT contain multi-dimensional arrays. Invalid item: "%s"', $item),
            );

            return array_map(self::mapParsedItem(...), explode(';', $arrayContent));
        }

        return preg_replace('/^[\'\"]?/', '', (string) preg_replace('/[\'\"]?$/', '', $item));
    }

    /**
     * Parse "(x, y, ... z)" string into Tuple(x, y, z) and validates items types
     * @see ITuple::match()
     * @see ITuple::parseMatchTypes()
     *
     * Types to match:
     * - string
     * - bool, boolean
     * - int, integer
     * - float, double
     * - array
     * - any, mixed, * (any is nullable by default)
     * - ?type (nullable type is any of the above with ? prefix)
     *
     * @example
     * Tuple::parseMatch('(foo, bar)', 'string', 'string')->toArray()        -> ['foo', 'bar']
     * Tuple::parseMatch('("foo bar", boo)', 'string', 'string')->toArray()  -> ['foo bar', 'boo']
     * Tuple::parseMatch('(1, 2, 3)', 'int', 'int', 'int')->toArray()        -> [1, 2, 3]
     *
     * Invalid (throws an \InvalidArgumentException):
     * Tuple::parseMatch('(1, 2, 3)', 'int', 'int')  // (int, int) expected but got (int, int, int)
     * Tuple::parseMatch('(1, 2)', 'int', 'string')  // (int, string) expected but got (int, int)
     *
     * @throws TupleMatchException
     * @throws TupleParseException
     */
    public static function parseMatch(string $tuple, string $typeFirst, string $typeSecond, string ...$type): ITuple
    {
        return self::parseMatchTypes($tuple, array_merge([$typeFirst, $typeSecond], $type));
    }

    /**
     * Parse "(x, y, ... z)" string into Tuple(x, y, z) and validates items types
     * @see ITuple::matchTypes()
     * @see ITuple::parseMatch()
     *
     * Types to match:
     * - string
     * - bool, boolean
     * - int, integer
     * - float, double
     * - array
     * - any, mixed, * (any is nullable by default)
     * - ?type (nullable type is any of the above with ? prefix)
     *
     * @example
     * Tuple::parseMatchTypes('(foo, bar)', ['string', 'string'])->toArray()        -> ['foo', 'bar']
     * Tuple::parseMatchTypes('("foo bar", boo)', ['string', 'string'])->toArray()  -> ['foo bar', 'boo']
     * Tuple::parseMatchTypes('(1, 2, 3)', ['int', 'int', 'int'])->toArray()        -> [1, 2, 3]
     *
     * Invalid (throws an \InvalidArgumentException):
     * Tuple::parseMatchTypes('(1, 2, 3)', ['int', 'int'])  // (int, int) expected but got (int, int, int)
     * Tuple::parseMatchTypes('(1, 2)', ['int', 'string'])  // (int, string) expected but got (int, int)
     *
     * @phpstan-param string[] $types
     *
     * @throws TupleMatchException
     * @throws TupleParseException
     */
    public static function parseMatchTypes(string $tuple, array $types): ITuple
    {
        /** @var self $parsedTuple */
        $parsedTuple = self::parse($tuple, count($types));

        if (!$parsedTuple->matchTypes($types)) {
            throw TupleMatchException::forTypes(...$parsedTuple->getTypes($types));
        }

        return $parsedTuple;
    }

    /**
     * @example
     * Tuple::of(1, 2, 3)->toArray() -> [1, 2, 3]
     * Tuple::of(...$array)->toArray() -> $array    // same as Tuple::from()
     */
    public static function of(mixed $first, mixed $second, mixed ...$value): ITuple
    {
        return new self(array_merge([$first, $second], $value));
    }

    /**
     * @example
     * Tuple::from([1, 2, 3])->toArray() -> [1, 2, 3]
     *
     * @phpstan-param mixed[] $values
     */
    public static function from(array $values): ITuple
    {
        return new self($values);
    }

    /**
     * Merge base tuple with additional items
     * @see ITuple::mergeMatch()
     *
     * You can merge base tuple with other values or with other tuples
     * Note: result of merging tuples will be flatten
     *
     * @example
     * Tuple::merge(Tuple::of(1, 2), 3)                                -> (1, 2, 3)
     * Tuple::merge(Tuple::of(1, 2), 3, 4)                             -> (1, 2, 3, 4)
     * Tuple::merge(Tuple::of(1, 2), Tuple::of(3, 4))                  -> (1, 2, 3, 4)
     * Tuple::merge(Tuple::of(1, 2), Tuple::of(3, 4), 5)               -> (1, 2, 3, 4, 5)
     * Tuple::merge(Tuple::of(1, 2), Tuple::of(3, 4), Tuple::of(5, 6)) -> (1, 2, 3, 4, 5, 6)
     */
    public static function merge(ITuple $base, mixed ...$additional): ITuple
    {
        return self::from(
            array_merge(
                $base->toArray(),
                Seq::init(function () use ($additional) {
                    foreach ($additional as $item) {
                        if ($item instanceof ITuple) {
                            yield from $item;
                        } else {
                            yield $item;
                        }
                    }
                })->toArray(),
            ),
        );
    }

    /**
     * Merge base tuple with additional items and checks whether result matches given types
     * @see ITuple::merge()
     * @see ITuple::matchTypes()
     *
     * Types to match:
     * - string
     * - bool, boolean
     * - int, integer
     * - float, double
     * - array
     * - any, mixed, * (any is nullable by default)
     * - ?type (nullable type is any of the above with ? prefix)
     *
     * @example
     * Tuple::mergeMatch(['string', 'string', 'string'], Tuple::parse('(foo, bar)'), 'boo')->toArray()  -> ['foo', 'bar', 'boo']
     * Tuple::mergeMatch(['int', 'int', 'int', 'string'], Tuple::parse('(1, 2, 3)'), 'four')->toArray() -> [1, 2, 3, 'four']
     *
     * Invalid (throws an \InvalidArgumentException):
     * Tuple::mergeMatch(['int', 'int'], Tuple::parse('(1, 2, 3)'), '4') // (int, int) expected but got (int, int, int, string)
     * Tuple::mergeMatch(['int', 'string'], Tuple::parse('(1, 2)'), 3)   // (int, string) expected but got (int, int, int)
     *
     * @phpstan-param string[] $types
     *
     * @throws TupleMatchException
     */
    public static function mergeMatch(array $types, ITuple $base, mixed ...$additional): ITuple
    {
        /** @var self $mergedTuple */
        $mergedTuple = self::merge($base, ...$additional);

        if (!$mergedTuple->matchTypes($types)) {
            throw TupleMatchException::forTypes(...$mergedTuple->getTypes($types));
        }

        return $mergedTuple;
    }

    /** @phpstan-param mixed[] $values */
    private function __construct(private array $values)
    {
        try {
            Assertion::greaterOrEqualThan(
                count($values),
                self::MINIMAL_TUPLE_ITEMS_COUNT,
                'Tuple must have at least two values.',
            );
        } catch (AssertionFailedException $e) {
            throw TupleException::forFailedAssertion($e);
        }
    }

    public function count(): int
    {
        return count($this->values);
    }

    /** @phpstan-return \Traversable<int, mixed> */
    public function getIterator(): \Traversable
    {
        yield from $this->toArray();
    }

    public function isEmpty(): bool
    {
        foreach ($this as $v) {
            if (!empty($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @see Tuple::toString()
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Transform tuple values into string (which is compatible with Tuple::parse() method)
     * @see Tuple::parse()
     */
    public function toString(): string
    {
        return $this->formatToString(
            ', ',
            '; ',
            fn (string $value): string => sprintf('"%s"', $value)
        );
    }

    private function formatToString(string $separator, string $arraySeparator, callable $formatStringValue): string
    {
        return sprintf(
            '(%s)',
            Seq::from($this->values)
                ->map($this->mapValueToString($arraySeparator, $formatStringValue))
                ->implode($separator),
        );
    }

    public function toStringForUrl(): string
    {
        return $this->formatToString(
            ',',
            ';',
            fn (string $value): string => $this->isMatching('/^[a-zA-Z0-9.\-_ ]+$/', $value)
                ? $value
                : sprintf('"%s"', $value)
        );
    }

    private function isMatching(string $pattern, string $value): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    private function mapValueToString(string $arraySeparator, callable $formatStringValue): callable
    {
        return function ($value) use ($arraySeparator, $formatStringValue) {
            if ($value === null) {
                return 'null';
            } elseif ($value === true) {
                return 'true';
            } elseif ($value === false) {
                return 'false';
            } elseif (is_array($value)) {
                return sprintf(
                    '[%s]',
                    Seq::from($value)
                        ->map($this->mapValueToString($arraySeparator, $formatStringValue))
                        ->implode($arraySeparator),
                );
            }

            return is_string($value)
                ? $formatStringValue($value)
                : $value;
        };
    }

    /** @phpstan-return mixed[] */
    public function toArray(): array
    {
        return array_values($this->values);
    }

    /**
     * Will return first value from tuple
     *
     * If you want to another values, you can use
     * @see Tuple::second()
     *
     * For third, fourth, ... use destructuring
     * [$_, $_, $third] = $tuple
     */
    public function first(): mixed
    {
        return $this->values[0] ?? null;
    }

    /**
     * Will return second value from tuple
     *
     * If you want to another values, you can use
     * @see Tuple::first()
     *
     * For third, fourth, ... use destructuring
     * [$_, $_, $third] = $tuple
     */
    public function second(): mixed
    {
        return $this->values[1] ?? null;
    }

    /**
     * Compares values of this Tuple with given Tuple and returns if they are same
     *
     * @example
     * Tuple::from([1, 2])->isSame(Tuple::from([1, 2])) // true
     * Tuple::from([1, 2])->isSame(Tuple::from([2, 1])) // false
     */
    public function isSame(ITuple $tuple): bool
    {
        return $this->toArray() === $tuple->toArray();
    }

    /**
     * Checks whether this Tuple matches given types
     * Types to match:
     * - string
     * - bool, boolean
     * - int, integer
     * - float, double
     * - array
     * - any, mixed, *
     * - ?type (nullable type is any of the above with ? prefix)
     *
     * @example
     * Tuple::from([1, 2])->match('int', 'int')        // true
     * Tuple::from([1, 'foo'])->match('int', 'string') // true
     * Tuple::from(['foo', 1])->match('int', 'string') // false
     */
    public function match(string $typeFirst, string $typeSecond, string ...$type): bool
    {
        return $this->matchTypes(array_merge([$typeFirst, $typeSecond], $type));
    }

    /**
     * Checks whether this Tuple matches given types
     * Types to match:
     * - string
     * - bool, boolean
     * - int, integer
     * - float, double
     * - array
     * - any, mixed, * (any is nullable by default)
     * - ?type (nullable type is any of the above with ? prefix)
     *
     * @example
     * Tuple::from([1, 2])->matchTypes(['int', 'int'])        // true
     * Tuple::from([1, 'foo'])->matchTypes(['int', 'string']) // true
     * Tuple::from(['foo', 1])->matchTypes(['int', 'string']) // false
     *
     * @phpstan-param string[] $types
     */
    public function matchTypes(array $types): bool
    {
        Assertion::greaterOrEqualThan(
            count($types),
            self::MINIMAL_TUPLE_ITEMS_COUNT,
            sprintf(
                'Tuples has always at least %d values. It would always be false by giving less then %d types.',
                self::MINIMAL_TUPLE_ITEMS_COUNT,
                self::MINIMAL_TUPLE_ITEMS_COUNT,
            ),
        );

        if (count($types) !== $this->count()) {
            return false;
        }

        [$expectedTypes, $actualTypes] = $this->getTypes($types);

        foreach ($expectedTypes as $i => $expectedType) {
            if ($expectedType === '*') {
                continue;
            }

            $atLeastOneTypeMatched = false;
            foreach (explode('|', $expectedType) as $expected) {
                if ($actualTypes[$i] === $expected) {
                    $atLeastOneTypeMatched = true;
                    break;
                }
            }

            if (!$atLeastOneTypeMatched) {
                return false;
            }
        }

        return true;
    }

    /**
     * @phpstan-param string[] $types
     * @phpstan-return array{0: string[], 1: string[]}
     */
    private function getTypes(array $types): array
    {
        $normalizeType = $this->normalizeType();

        /** @phpstan-var string[] $expectedTypes */
        $expectedTypes = Seq::create($types, $normalizeType)
            ->toArray();
        $actualTypes = Seq::create($this->toArray(), gettype(...))
            ->map($normalizeType)
            ->toArray();

        return [$expectedTypes, $actualTypes];
    }

    /** @phpstan-return \Closure(string): string */
    private function normalizeType(): \Closure
    {
        return fn (string $type): string => Seq::create(
            explode('|', $type),
            function (string $type): iterable {
                if (str_starts_with($type, '?')) {
                    yield 'NULL';
                }

                yield ltrim($type, '?');
            },
        )
            ->map(fn (string $type) => match ($type) {
                'integer' => 'int',
                'boolean' => 'bool',
                'double' => 'float',
                'any', 'mixed' => '*',
                default => $type,
            })
            ->unique()
            ->implode('|');
    }

    /**
     * @deprecated Altering existing tuple is not permitted
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->forbiddenMethod();
    }

    private function forbiddenMethod(): void
    {
        throw TupleBadMethodCallException::forAlteringTuple();
    }

    /**
     * @deprecated Altering existing tuple is not permitted
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->forbiddenMethod();
    }

    /**
     * @param int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        self::assertKey($offset);

        return array_key_exists($offset, $this->values);
    }

    private static function assertKey(mixed $key): void
    {
        Assertion::integer($key, 'Tuples can only have integer indexes.');
    }

    /**
     * @param int $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        self::assertKey($offset);

        return $this->values[$offset];
    }
}
