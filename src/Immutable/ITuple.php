<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

interface ITuple extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Parse "(x, y, ... z)" string into Tuple(x, y, z)
     *
     * @example
     * Tuple::parse('(foo, bar)')->toArray() -> ['foo', 'bar']
     * Tuple::parse('("foo bar")')->toArray() -> ['foo bar']
     * Tuple::parse('(1, 2, 3)')->toArray() -> [1, 2, 3]
     */
    public static function parse(string $tuple): self;

    /**
     * @example
     * Tuple::of(1, 2, 3)->toArray() -> [1, 2, 3]
     * Tuple::of(...$array)->toArray() -> $array    // same as Tuple::from()
     *
     * @param mixed $first
     * @param mixed $second
     */
    public static function of($first, $second, ...$value): self;

    /**
     * @example
     * Tuple::from([1, 2, 3])->toArray() -> [1, 2, 3]
     */
    public static function from(array $values): self;

    /**
     * @see Tuple::toString()
     */
    public function __toString(): string;

    /**
     * Transform tuple values into string (which is compatible with Tuple::parse() method)
     * @see Tuple::parse()
     */
    public function toString(): string;

    public function toArray(): array;

    /**
     * Will return first value from tuple
     *
     * If you want to another values, you can use
     * @see Tuple::second()
     *
     * For third, fourth, ... use destructuring
     * [, , $third] = $tuple
     *
     * @return mixed
     */
    public function first();

    /**
     * Will return second value from tuple
     *
     * If you want to another values, you can use
     * @see Tuple::first()
     *
     * For third, fourth, ... use destructuring
     * [, , $third] = $tuple
     *
     * @return mixed
     */
    public function second();

    /**
     * Compares values of this Tuple with given Tuple and returns if they are same
     *
     * @example
     * Tuple::from([1, 2])->isSame(Tuple::from([1, 2])) // true
     * Tuple::from([1, 2])->isSame(Tuple::from([2, 1])) // false
     */
    public function isSame(ITuple $tuple): bool;

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
    public function match(string $typeFirst, string $typeSecond, string ...$type): bool;

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
     * Tuple::from([1, 2])->matchTypes(['int', 'int']) // true
     * Tuple::from([1, 'foo'])->matchTypes(['int', 'string']) // true
     * Tuple::from(['foo', 1])->matchTypes(['int', 'string']) // false
     */
    public function matchTypes(array $types): bool;

    /** @deprecated Altering existing tuple is not permitted */
    public function offsetSet($offset, $value): void;

    /** @deprecated Altering existing tuple is not permitted */
    public function offsetUnset($offset): void;
}
