<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use Assert\Assertion;

class Tuple implements ITuple
{
    /** @var array */
    private $values;

    /**
     * Parse "(x, y, ... z)" string into Tuple(x, y, z)
     *
     * @example
     * Tuple::parse('(foo, bar)')->toArray() -> ['foo', 'bar']
     * Tuple::parse('("foo bar")')->toArray() -> ['foo bar']
     * Tuple::parse('(1, 2, 3)')->toArray() -> [1, 2, 3]
     */
    public static function parse(string $tuple): ITuple
    {
        if (empty($tuple)) {
            return new self([]);
        }

        $cache = [];
        $parts = Seq::init(function () use ($tuple) {
            return explode(',', trim($tuple, '()'));
        })
            ->reduce(function (array $matches, string $match) use (&$cache) {
                $isStart = in_array(mb_substr(ltrim($match), 0, 1), ['"', "'"], true)
                    && empty($cache);
                $isEnd = in_array(mb_substr($match, -1, 1), ['"', "'"], true)
                    && (mb_strlen(ltrim($match)) > 1 || !empty($cache));

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

        $values = Seq::from($parts)
            ->map(function (string $match) {
                return trim($match);
            })
            ->filter('($match) => $match !== ""')
            ->map(function (string $item) {
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
                }

                return preg_replace('/^[\'\"]?/', '', preg_replace('/[\'\"]?$/', '', $item));
            })
            ->toArray();

        return new self($values);
    }

    /**
     * @example
     * Tuple::of(1, 2, 3)->toArray() -> [1, 2, 3]
     * Tuple::of(...$array)->toArray() -> $array    // same as Tuple::from()
     *
     * @param mixed $first
     * @param mixed $second
     */
    public static function of($first, $second, ...$value): ITuple
    {
        return new self(array_merge([$first, $second], $value));
    }

    /**
     * @example
     * Tuple::from([1, 2, 3])->toArray() -> [1, 2, 3]
     */
    public static function from(array $values): ITuple
    {
        return new self($values);
    }

    private function __construct(array $values)
    {
        Assertion::greaterOrEqualThan(
            count($values),
            2,
            sprintf('Tuple must have at least two values. Given "%s".', var_export($values, true))
        );
        $this->values = $values;
    }

    /**
     * @param int $offset
     * @return bool true on success or false on failure
     */
    public function offsetExists($offset)
    {
        self::assertKey($offset);

        return array_key_exists($offset, $this->values);
    }

    private static function assertKey($key): void
    {
        Assertion::integer($key, 'Tuples can only have integer indexes.');
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        self::assertKey($offset);

        return $this->values[$offset];
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
        return empty($this->values)
            ? '()'
            : sprintf('(%s)', implode(', ', array_map(function ($value) {
                if ($value === null) {
                    return 'null';
                } elseif ($value === true) {
                    return 'true';
                } elseif ($value === false) {
                    return 'false';
                }

                return is_string($value)
                    ? sprintf('"%s"', $value)
                    : $value;
            }, $this->values)));
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function getIterator(): iterable
    {
        yield from $this->toArray();
    }

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
     *
     * @return mixed
     */
    public function first()
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
     *
     * @return mixed
     */
    public function second()
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
     * Tuple::from([1, 2])->match('int', 'int') // true
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
     * Tuple::from([1, 2])->matchTypes(['int', 'int']) // true
     * Tuple::from([1, 'foo'])->matchTypes(['int', 'string']) // true
     * Tuple::from(['foo', 1])->matchTypes(['int', 'string']) // false
     */
    public function matchTypes(array $types): bool
    {
        Assertion::greaterOrEqualThan(
            count($types),
            2,
            'Tuples has always at least two values. It would always be false by giving less then 2 types.'
        );

        if (count($types) !== $this->count()) {
            return false;
        }

        $normalizeType = function (string $type): string {
            return $this->normalizeType($type);
        };

        $expectedTypes = Seq::create($types, $normalizeType);
        $actualTypes = Seq::create($this->toArray(), 'gettype')
            ->map($normalizeType)
            ->toArray();

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

    private function normalizeType(string $type): string
    {
        $types = Seq::create(explode('|', $type), function (string $type): iterable {
            if (mb_substr($type, 0, 1) === '?') {
                yield 'NULL';
            }

            yield ltrim($type, '?');
        })
            ->map(function ($type) {
                switch ($type) {
                    case 'integer':
                        return 'int';
                    case 'boolean':
                        return 'bool';
                    case 'double':
                        return 'float';
                    case 'any':
                    case 'mixed':
                        return '*';
                    default:
                        return $type;
                }
            });

        return implode('|', array_unique($types->toArray()));
    }

    /** @deprecated Altering existing tuple is not permitted */
    public function offsetSet($offset, $value): void
    {
        $this->forbiddenMethod();
    }

    private function forbiddenMethod(): void
    {
        throw new \BadMethodCallException('Altering existing tuple is not permitted.');
    }

    /** @deprecated Altering existing tuple is not permitted */
    public function offsetUnset($offset): void
    {
        $this->forbiddenMethod();
    }
}
