<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use MF\Collection\Exception\TupleMatchException;
use MF\Collection\Exception\TupleParseException;
use MF\Collection\Generic\IEnumerable;

/**
 * @phpstan-type TIndex int
 * @phpstan-type TValue mixed
 *
 * @phpstan-extends IEnumerable<TIndex, TValue>
 * @phpstan-extends \ArrayAccess<TIndex, TValue>
 */
interface ITuple extends IEnumerable, \ArrayAccess, \Stringable
{
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
    public static function parse(string $tuple, int $expectedItemsCount = null): self;

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
     * @throws TupleParseException
     * @throws TupleMatchException
     */
    public static function parseMatch(string $tuple, string $typeFirst, string $typeSecond, string ...$type): self;

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
     * @throws TupleParseException
     * @throws TupleMatchException
     */
    public static function parseMatchTypes(string $tuple, array $types): self;

    /**
     * @example
     * Tuple::of(1, 2, 3)->toArray() -> [1, 2, 3]
     * Tuple::of(...$array)->toArray() -> $array    // same as Tuple::from()
     */
    public static function of(mixed $first, mixed $second, mixed ...$value): self;

    /**
     * @example
     * Tuple::from([1, 2, 3])->toArray() -> [1, 2, 3]
     *
     * @phpstan-param mixed[] $values
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

    /**
     * Transform tuple values into string which is supposed to be nice in URL and still compatible with Tuple::parse() method
     * - no spaces
     * - no superfluous quotes
     *
     * @see Tuple::parse()
     */
    public function toStringForUrl(): string;

    /** @phpstan-return mixed[] */
    public function toArray(): array;

    public static function fst(ITuple $tuple): mixed;

    public static function snd(ITuple $tuple): mixed;

    /**
     * Will return first value from tuple
     *
     * If you want to another values, you can use
     * @see Tuple::second()
     *
     * For third, fourth, ... use destructuring
     * [, , $third] = $tuple
     */
    public function first(): mixed;

    /**
     * Will return second value from tuple
     *
     * If you want to another values, you can use
     * @see Tuple::first()
     *
     * For third, fourth, ... use destructuring
     * [, , $third] = $tuple
     */
    public function second(): mixed;

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
     * Tuple::from([1, 2])->match('int', 'int')        // true
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
     * Tuple::from([1, 2])->matchTypes(['int', 'int'])        // true
     * Tuple::from([1, 'foo'])->matchTypes(['int', 'string']) // true
     * Tuple::from(['foo', 1])->matchTypes(['int', 'string']) // false
     *
     * @phpstan-param string[] $types
     */
    public function matchTypes(array $types): bool;

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
    public static function merge(ITuple $base, mixed ...$additional): ITuple;

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
    public static function mergeMatch(array $types, ITuple $base, mixed ...$additional): ITuple;

    /** @deprecated Altering existing tuple is not permitted */
    public function offsetSet(mixed $offset, mixed $value): void;

    /** @deprecated Altering existing tuple is not permitted */
    public function offsetUnset(mixed $offset): void;
}
