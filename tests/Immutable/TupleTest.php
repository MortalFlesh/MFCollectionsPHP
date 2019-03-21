<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use Eris\Generator;
use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Exception\TupleBadMethodCallException;
use MF\Collection\Exception\TupleExceptionInterface;
use MF\Collection\Exception\TupleMatchException;
use MF\Collection\Exception\TupleParseException;

class TupleTest extends AbstractTestCase
{
    public function testShouldParse(): void
    {
        $tuple = Tuple::parse('""", 1');
        $this->assertSame(['"', 1], $tuple->toArray());
    }

    /** @dataProvider provideArrayTuple */
    public function testShouldCreateTupleFromArray(array $array, array $expectedArray, string $expectedString): void
    {
        $tuple = Tuple::from($array);
        $asString = (string) $tuple;
        $tuple2 = Tuple::parse($asString);

        $this->assertSame($expectedString, $asString);
        $this->assertSame($expectedArray, $tuple->toArray());
        $this->assertSame($tuple->toString(), $tuple2->toString());
        $this->assertSame($tuple->toArray(), $tuple2->toArray());
    }

    public function provideArrayTuple(): array
    {
        return [
            // array, extected
            'ints' => [[1, 2, 3], [1, 2, 3], '(1, 2, 3)'],
            'empty strings' => [['', ''], ['', ''], '("", "")'],
            '"", 0' => [['', 0], ['', 0], '("", 0)'],
            '"", """' => [['', '"'], ['', '"'], '("", """)'],
            'array' => [[[1, 2], 'three', ['four']], [[1, 2], 'three', ['four']], '([1; 2], "three", ["four"])'],
        ];
    }

    public function testShouldCreateTupleFromAnyArray(): void
    {
        try {
            $this
                ->forAll(Generator\seq(Generator\oneOf(
                    Generator\string(),
                    Generator\int(),
                    Generator\float(),
                    Generator\bool()
                )))
                ->when(function (array $values) {
                    $count = count($values);

                    return $count >= 2 && $count < 50;
                })
                ->then(function (array $values): void {
                    $tuple = Tuple::from($values);
                    $result = $tuple->toArray();

                    $this->assertSame(
                        $values,
                        $result,
                        $this->pbtMessage($values, $result, 'are not same')
                    );
                });
        } catch (\OutOfBoundsException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    public function testShouldCreateTupleFromArrayTransformToStringAndBackToTupleAgain(): void
    {
        $filterNotIn = function (array $data) {
            return function ($item) use ($data) {
                return !in_array($item, $data, true);
            };
        };

        try {
            $this
                ->forAll(Generator\seq(Generator\oneOf(
                    Generator\string(),
                    Generator\int(),
                    Generator\bool()
                )))
                ->when(function (array $values) {
                    $count = count($values);

                    return $count >= 2 && $count < 50;
                })
                ->then(function (array $values) use ($filterNotIn): void {
                    $asString = Tuple::from($values)->toString();
                    $result = Tuple::parse($asString)->toArray();

                    $valuesCount = count($values);
                    $resultCount = count($result);

                    if ($valuesCount > $resultCount) {
                        // this case means that some values have unfinished string definition and another value too
                        // and the combined together while parsing
                        // or that some value end or start with , and is not in ""

                        // reproduce with:
                        // ERIS_SEED=1530111780272593 vendor/bin/phpunit --filter 'MF\\Collection\\Immutable\\TupleTest::testShouldCreateTupleFromArrayTransformToStringAndBackToTupleAgain'

                        //var_dump([
                        //    $values,
                        //    array_filter($result, $filterNotIn($values)),
                        //    array_filter($values, $filterNotIn($result)),
                        //]);
                        $this->assertTrue(true);
                    } elseif ($valuesCount === $resultCount) {
                        $this->assertSame(
                            $values,
                            $result,
                            $this->pbtMessage($values, $result, sprintf('are not same (as string "%s")', $asString))
                        );
                    } else {
                        // original values contained some complex string with , and was parsed into more values
                        $resultValuesNotFromOriginalValues = array_filter($result, $filterNotIn($values));
                        $originalValuesNotInResult = array_filter($values, $filterNotIn($result));

                        // Count of parsed values should be greater then original items count
                        $this->assertTrue(count($originalValuesNotInResult) < count($resultValuesNotFromOriginalValues));

                        // and all parsed values should be found in original set
                        $originalSet = implode('', $originalValuesNotInResult);
                        foreach ($resultValuesNotFromOriginalValues as $value) {
                            $this->assertStringContainsString(
                                $value,
                                $originalSet,
                                $this->pbtMessage(
                                    $originalValuesNotInResult,
                                    $resultValuesNotFromOriginalValues,
                                    sprintf('not contained %s', $value)
                                )
                            );
                        }
                    }
                });
        } catch (\OutOfBoundsException $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    public function testShouldDeconstructTuple(): void
    {
        $tuple = Tuple::of('foo', 'bar');

        [$foo, $bar] = $tuple;
        $this->assertSame('foo', $foo);
        $this->assertSame('bar', $bar);

        foreach ([$tuple] as [$foo, $bar]) {
            $this->assertSame('foo', $foo);
            $this->assertSame('bar', $bar);
        }
    }

    /** @dataProvider provideTuplesInString */
    public function testShouldParseTupleFromString(string $tuple, array $expectedArray, string $expectedString): void
    {
        $result = Tuple::parse($tuple);

        $this->assertSame($expectedArray, $result->toArray());
        $this->assertSame($expectedString, $result->toString());
    }

    public function provideTuplesInString(): array
    {
        return [
            // tuple, expectedArray, expectedString
            'nulls' => ['(null, null)', [null, null], '(null, null)'],
            'bools' => ['(true, false)', [true, false], '(true, false)'],
            'integers' => ['(1, 2, 3)', [1, 2, 3], '(1, 2, 3)'],
            'floats' => ['(1.1, 2.3)', [1.1, 2.3], '(1.1, 2.3)'],
            'strings' => [
                '(one, two, three, four)',
                ['one', 'two', 'three', 'four'],
                '("one", "two", "three", "four")',
            ],
            'without parentheses integers' => ['1, 2, 3', [1, 2, 3], '(1, 2, 3)'],
            'without parentheses floats' => ['1.1, 2.3', [1.1, 2.3], '(1.1, 2.3)'],
            'without parentheses strings' => [
                'one, two, three, four',
                ['one', 'two', 'three', 'four'],
                '("one", "two", "three", "four")',
            ],
            'complex strings' => [
                '("some complex string", two, three)',
                ['some complex string', 'two', 'three'],
                '("some complex string", "two", "three")',
            ],
            'complex strings 2' => ["('Horse Soldiers',USA)", ['Horse Soldiers', 'USA'], '("Horse Soldiers", "USA")'],
            'complex strings 3' => ["('one, two', three)", ['one, two', 'three'], '("one, two", "three")'],
            'complex strings 4' => ['("one, two", three)', ['one, two', 'three'], '("one, two", "three")'],
            'mixed' => ['(one, 2, 4.2)', ['one', 2, 4.2], '("one", 2, 4.2)'],
            'array' => ['(18,30,[DD;D])', [18, 30, ['DD', 'D']], '(18, 30, ["DD"; "D"])'],
            'two arrays' => [
                '(["Hello"; "world"],[from; array])',
                [['Hello', 'world'], ['from', 'array']],
                '(["Hello"; "world"], ["from"; "array"])',
            ],

            // potentially buggy behaviour with more commas without values
            'empty strings' => ['("",,1)', ['', 1], '("", 1)'],
        ];
    }

    /** @dataProvider provideTuplesInString */
    public function testShouldParseTupleFromStringAndExpectCorrectNumberOfItems(
        string $tuple,
        array $expectedArray,
        string $expectedString
    ): void {
        $result = Tuple::parse($tuple, count($expectedArray));

        $this->assertSame($expectedArray, $result->toArray());
        $this->assertSame($expectedString, $result->toString());
    }

    public function testShouldThrowInvalidArgumentExceptionOnTryToParseTupleWithInvalidExpectation(): void
    {
        $this->expectException(TupleParseException::class);
        $this->expectExceptionMessage('Expected items count is 1 but must not be lower than 2 because in that case it would not be a valid Tuple.');

        Tuple::parse('(1,2,3)', 1);
    }

    /** @dataProvider provideInvalidParseItems */
    public function testShouldNotParseTupleFromStringWithIncorrectNumberOfItems(
        string $tuple,
        int $expectedCount,
        string $expectedMessage
    ): void {
        $this->expectException(TupleParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        Tuple::parse($tuple, $expectedCount);
    }

    public function provideInvalidParseItems(): array
    {
        return [
            // tuple, expectedItemsCount, expectedMessage
            'nulls' => [
                '(null, null)',
                3,
                'Invalid tuple given - expected 3 items but parsed 2 items from "(null, null)".',
            ],
            'bools' => [
                '(true, false)',
                3,
                'Invalid tuple given - expected 3 items but parsed 2 items from "(true, false)".',
            ],
            'integers' => [
                '(1, 2, 3)',
                4,
                'Invalid tuple given - expected 4 items but parsed 3 items from "(1, 2, 3)".',
            ],
            'floats' => [
                '(1.1, 2.3, 5.2)',
                5,
                'Invalid tuple given - expected 5 items but parsed 3 items from "(1.1, 2.3, 5.2)".',
            ],
            'strings' => [
                '(one, two, three, four)',
                3,
                'Invalid tuple given - expected 3 items but parsed 4 items from "(one, two, three, four)".',
            ],
            'without parentheses integers' => [
                '1, 2, 3',
                4,
                'Invalid tuple given - expected 4 items but parsed 3 items from "1, 2, 3".',
            ],
            'without parentheses strings' => [
                'one, two, three, four',
                5,
                'Invalid tuple given - expected 5 items but parsed 4 items from "one, two, three, four".',
            ],
            'complex strings' => [
                '("some complex string", two, three)',
                2,
                'Invalid tuple given - expected 2 items but parsed 3 items from "("some complex string", two, three)".',
            ],
            'mixed' => [
                '(one, 2, 4.2)',
                4,
                'Invalid tuple given - expected 4 items but parsed 3 items from "(one, 2, 4.2)".',
            ],
            'array' => [
                '(one, [2;3;4])',
                4,
                'Invalid tuple given - expected 4 items but parsed 2 items from "(one, [2;3;4])".',
            ],

            // potentially buggy behaviour with more commas without values
            'empty strings' => [
                '("",,1)',
                3,
                'Invalid tuple given - expected 3 items but parsed 2 items from "("",,1)".',
            ],
        ];
    }

    /** @dataProvider provideParseTypes */
    public function testShouldParseTupleFromStringWithCorrectTypes(
        string $tuple,
        array $expectedTypes,
        array $expected
    ): void {
        $tuple = Tuple::parseMatchTypes($tuple, $expectedTypes);

        $this->assertSame($expected, $tuple->toArray());
    }

    public function provideParseTypes(): array
    {
        return [
            // tuple, expectedTypes, expected
            'nulls' => [
                '(null, null)',
                ['?string', '?string'],
                [null, null],
            ],
            'bools' => [
                '(true, false, null)',
                ['bool', 'boolean', '?boolean'],
                [true, false, null],
            ],
            'integers' => [
                '(1, 2, 3)',
                ['int', 'int', 'int'],
                [1, 2, 3],
            ],
            'floats' => [
                '(1.1, 2.3, 5.2)',
                ['float', 'float', 'float'],
                [1.1, 2.3, 5.2],
            ],
            'strings' => [
                '(one, two, three, four)',
                ['string', 'string', 'string', 'string'],
                ['one', 'two', 'three', 'four'],
            ],
            'without parentheses integers' => [
                '1, 2, 3',
                ['int', 'int', 'int'],
                [1, 2, 3],
            ],
            'without parentheses strings' => [
                'one, two, three, four',
                ['string', 'string', 'string', 'string'],
                ['one', 'two', 'three', 'four'],
            ],
            'complex strings' => [
                '("some complex string", two, three)',
                ['string', 'string', 'string'],
                ['some complex string', 'two', 'three'],
            ],
            'mixed' => [
                '(one, 2, 4.2)',
                ['?string', 'int', 'float'],
                ['one', 2, 4.2],
            ],
            'any' => [
                '(one, 2)',
                ['string', 'any'],
                ['one', 2],
            ],
            'array' => [
                '(one, [2; 3])',
                ['string', 'array'],
                ['one', [2, 3]],
            ],

            // potentially buggy behaviour with more commas without values
            'empty strings' => [
                '("",,1)',
                ['string', 'int'],
                ['', 1],
            ],
        ];
    }

    public function testShouldNotParseTupleFromStringWithIncorrectType(): void
    {
        $this->expectException(TupleMatchException::class);
        $this->expectExceptionMessage('Given tuple does NOT match expected types (string, int) - got (string, string).');

        Tuple::parseMatch('(foo, bar)', 'string', 'int');
    }

    /** @dataProvider provideInvalidParseTypes */
    public function testShouldNotParseTupleFromStringWithIncorrectTypes(
        string $tuple,
        array $expectedTypes,
        string $expectedException,
        string $expectedMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        Tuple::parseMatchTypes($tuple, $expectedTypes);
    }

    public function provideInvalidParseTypes(): array
    {
        return [
            // tuple, expectedTypes, expectedMessage
            'nulls' => [
                '(null, null)',
                ['string', 'string'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (string, string) - got (NULL, NULL).',
            ],
            'bools' => [
                '(true, false)',
                ['string', 'string'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (string, string) - got (bool, bool).',
            ],
            'integers' => [
                '(1, 2, 3)',
                ['int', 'int'],
                TupleParseException::class,
                'Invalid tuple given - expected 2 items but parsed 3 items from "(1, 2, 3)".',
            ],
            'floats' => [
                '(1.1, 2.3, 5.2)',
                ['int', 'int', 'int'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (int, int, int) - got (float, float, float).',
            ],
            'strings' => [
                '(one, two, three, four)',
                ['string', 'string', 'int'],
                TupleParseException::class,
                'Invalid tuple given - expected 3 items but parsed 4 items from "(one, two, three, four)".',
            ],
            'without parentheses integers' => [
                '1, 2, 3',
                ['int', 'int', 'float'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (int, int, float) - got (int, int, int).',
            ],
            'without parentheses strings' => [
                'one, two, three, four',
                ['string', 'string', 'int', 'string'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (string, string, int, string) - got (string, string, string, string).',
            ],
            'complex strings' => [
                '("some complex string", two, three)',
                ['string', 'string', 'int'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (string, string, int) - got (string, string, string).',
            ],
            'mixed' => [
                '(one, 2, 4.2)',
                ['string', 'int', 'int'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (string, int, int) - got (string, int, float).',
            ],
            'array' => [
                '(one, [2; 3], 4.2)',
                ['string', 'array', 'int'],
                TupleMatchException::class,
                'Given tuple does NOT match expected types (string, array, int) - got (string, array, float).',
            ],

            // potentially buggy behaviour with more commas without values
            'empty strings' => [
                '("",,1)',
                ['string', 'string', 'int'],
                TupleParseException::class,
                'Invalid tuple given - expected 3 items but parsed 2 items from "("",,1)".',
            ],
        ];
    }

    /** @dataProvider provideInvalidParse */
    public function testShouldNotParseTuples(string $invalidTupleString, string $expectedMessage): void
    {
        $this->expectException(TupleParseException::class);
        $this->expectExceptionMessage($expectedMessage);

        Tuple::parse($invalidTupleString);
    }

    public function provideInvalidParse(): array
    {
        $singleItemException = 'Tuple must have at least two values.';

        return [
            // invalid parse, expectedMessage
            'empty string' => ['', $singleItemException],
            'empty' => ['()', $singleItemException],
            'one int' => ['(1)', $singleItemException],
            'one string' => ['(foo)', $singleItemException],
            'one complex string' => ['("foo, bar")', $singleItemException],
            'one bool' => ['("false")', $singleItemException],

            'more dimensional arrays' => [
                '(foo, [bar; [boo; [baf; baf]]])',
                'Tuple must NOT contain multi-dimensional arrays. Invalid item: "[bar; [boo; [baf; baf]]]"',
            ],

            // potentially buggy behaviour with more commas without values
            'empty values' => ['(,)', $singleItemException],
        ];
    }

    /** @dataProvider provideInvalidTuple */
    public function testShouldNotCreateTuples(array $invalidTuple): void
    {
        $this->expectException(TupleExceptionInterface::class);
        $this->expectExceptionMessage('Tuple must have at least two values.');

        Tuple::from($invalidTuple);
    }

    public function provideInvalidTuple(): array
    {
        return [
            // invalid from
            'empty - array' => [[]],
            'one item' => [['string']],
            'one int' => [[1]],
        ];
    }

    public function testShouldTryToFindValue(): void
    {
        $tuple = Tuple::of('foo', 'bar');

        $this->assertArrayHasKey(0, $tuple);
        $this->assertArrayHasKey(1, $tuple);
        $this->assertArrayNotHasKey(2, $tuple);
    }

    public function testShouldNotAddValueToTuple(): void
    {
        $foo = Tuple::of('foo', 'bar');

        $this->expectException(TupleBadMethodCallException::class);
        $this->expectExceptionMessage('Altering existing tuple is not permitted.');

        $foo->offsetSet(0, 'bar');
    }

    public function testShouldNotAddValueToTupleByArrayNotation(): void
    {
        $foo = Tuple::of('foo', 'bar');

        $this->expectException(TupleBadMethodCallException::class);
        $this->expectExceptionMessage('Altering existing tuple is not permitted.');
        $foo[] = 'bar';
    }

    public function testShouldNotUnsetValueFromTuple(): void
    {
        $foo = Tuple::of('foo', 'bar');

        $this->expectException(TupleBadMethodCallException::class);
        $this->expectExceptionMessage('Altering existing tuple is not permitted.');
        $foo->offsetUnset(0);
    }

    public function testShouldNotUnsetValueFromTupleByArrayNotation(): void
    {
        $foo = Tuple::of('foo', 'bar');

        $this->expectException(TupleBadMethodCallException::class);
        $this->expectExceptionMessage('Altering existing tuple is not permitted.');
        unset($foo[0]);
    }

    public function testShouldTransformToArrayNatively(): void
    {
        $tupleString = (string) Tuple::of(1, 2, 3);

        $this->assertSame('(1, 2, 3)', $tupleString);
    }

    /** @dataProvider provideTupleCount */
    public function testShouldCountTupleValues(array $input, int $expectedCount): void
    {
        $tuple = Tuple::from($input);

        $this->assertCount($expectedCount, $tuple);
        $this->assertSame($expectedCount, $tuple->count());
        $this->assertSame($expectedCount, count($tuple));
    }

    public function provideTupleCount(): array
    {
        return [
            // tupleInput, expectedCount
            'ints' => [[1, 2], 2],
            'mixed' => [['value', 1, 1.2, true], 4],
            'array' => [['value', [1, 1.2], true], 3],
        ];
    }

    public function testShouldGetFirstValueFromTuple(): void
    {
        $tuple = Tuple::parse('(1, 2, 3)');

        $first = $tuple->first();

        $this->assertSame(1, $first);
    }

    public function testShouldGetSecondValueFromTuple(): void
    {
        $tuple = Tuple::parse('(1, 2, 3)');

        $first = $tuple->second();

        $this->assertSame(2, $first);
    }

    public function testShouldGetNthValueFromTuple(): void
    {
        $tuple = Tuple::parse('(1, 2, 3)');

        [, , $third] = $tuple;

        $this->assertSame(3, $third);
    }

    /** @dataProvider provideSameTuples */
    public function testShouldCheckWhetherAreTuplesSame(ITuple $one, ITuple $two, bool $shouldBeSame): void
    {
        $result = $one->isSame($two);

        $this->assertSame($shouldBeSame, $result);
    }

    public function provideSameTuples(): array
    {
        return [
            // one, two, shouldBeSame
            'same ints' => [Tuple::from([1, 2]), Tuple::of(1, 2), true],
            'same strings' => [Tuple::from(['foo', 'bar']), Tuple::parse('(foo, bar)'), true],
            'same mixed' => [Tuple::from([1, 'bar']), Tuple::parse('(1, bar)'), true],
            'different order - ints' => [Tuple::from([1, 2]), Tuple::parse('(2, 1)'), false],
            'different order - strings' => [Tuple::from(['bar', 'foo']), Tuple::of('foo', 'bar'), false],
            'different' => [Tuple::from([1, 'foo', true]), Tuple::parse('(4, 5)'), false],
            'different arrays' => [Tuple::from([[1, 2], 'foo', true]), Tuple::parse('(1, 2, foo, true)'), false],
            'same arrays' => [Tuple::from([[1, 2], 'foo', true]), Tuple::parse('([1; 2], foo, true)'), true],
        ];
    }

    public function testShouldCheckWhetherTupleMatchesType(): void
    {
        $tuple = Tuple::of('foo', 1);

        $this->assertTrue($tuple->match('string', 'int'));
    }

    public function testShouldCheckWhetherTupleMatchesTypeOfMoreValues(): void
    {
        $tuple = Tuple::of('foo', 1, ['array']);

        $this->assertTrue($tuple->match('string', 'int', 'array'));
    }

    /** @dataProvider provideTuplesToMatch */
    public function testShouldCheckWhetherTuplesMatchesTypes(
        ITuple $tuple,
        array $typesToMatch,
        bool $shouldMatch
    ): void {
        $result = $tuple->matchTypes($typesToMatch);

        $this->assertSame($shouldMatch, $result);
    }

    public function provideTuplesToMatch(): array
    {
        return [
            // tuple, typesToMatch, shouldMatch
            'ints' => [Tuple::from([1, 2]), ['int', 'int'], true],
            'integers' => [Tuple::from([1, 2]), ['integer', 'int'], true],
            'nullable ints' => [Tuple::from([1, 2, 1, null]), ['?int', '?integer', 'int', '?integer'], true],
            'bools' => [Tuple::from([1, true]), ['int', 'bool'], true],
            'booleans' => [Tuple::from([1, true]), ['integer', 'boolean'], true],
            'strings' => [Tuple::from(['foo', 'bar']), ['string', 'string'], true],
            'mixed' => [Tuple::from([1, 'bar', 2.1]), ['int', 'string', 'float'], true],
            'parsed' => [
                Tuple::parse('("complex string", string, 1, 2.1, true)'),
                ['string', 'string', 'int', 'float', 'bool'],
                true,
            ],
            'nullable array' => [Tuple::from([1, null]), ['int|array', '?array'], true],
            'not match - ints' => [Tuple::from([1, 2]), ['int', 'int', 'string'], false],
            'not match - order' => [Tuple::from([1, 'string']), ['string', 'int'], false],
            'not match - mixed' => [Tuple::from([1, 'foo', true]), ['float', 'string', 'bool'], false],
            'not match - less types' => [Tuple::from([1, 'foo', true]), ['int', 'string'], false],
            'not match - more types' => [Tuple::from([1, 'foo', true]), ['int', 'string', 'bool', 'any'], false],
            // wildcard
            'matched - mixed' => [Tuple::from([1, 'foo', true]), ['int', 'mixed', 'bool'], true],
            'matched - any' => [Tuple::from([1, 'foo', true]), ['int', 'any', 'bool'], true],
            'matched - any with null' => [Tuple::from([1, null, true]), ['int', 'any', 'bool'], true],
            'matched - any with null - array' => [Tuple::from([[1], null]), ['array', 'any'], true],
            'matched - any with array' => [Tuple::from([[1], null]), ['any', '*'], true],
            // by parse
            'parse - nulls - int' => [Tuple::parse('(null, null)'), ['int', 'int'], false],
            'parse - nulls - ?int' => [Tuple::parse('(null, null)'), ['?int', '?int'], true],
            'parse - nulls - string' => [Tuple::parse('(null, null)'), ['string', 'string'], false],
            'parse - nulls - ?string' => [Tuple::parse('(null, null)'), ['?string', '?string'], true],
            'parse - ints' => [Tuple::parse('(1, 2)'), ['int', 'int'], true],
            'parse - integers' => [Tuple::parse('(1, 42)'), ['integer', 'int'], true],
            'parse - bools' => [Tuple::parse('(1, true)'), ['int', 'bool'], true],
            'parse - booleans' => [Tuple::parse('1, true'), ['integer', 'boolean'], true],
            'parse - strings' => [Tuple::parse("'foo', 'bar'"), ['string', 'string'], true],
            'parse - mixed' => [Tuple::parse('(1, \'bar\', 2.1)'), ['int', 'string', 'float'], true],
            'parse - not match - ints' => [Tuple::parse('(1, 2'), ['int', 'int', 'string'], false],
            'parse - not match - order' => [Tuple::parse('(1, \'string\')'), ['string', 'int'], false],
            'parse - not match - mixed' => [Tuple::parse('(1, foo, true)'), ['float', 'string', 'bool'], false],
            // multiple types
            'ints|string 1' => [Tuple::from(['foo', 2]), ['string|int', 'string|?int'], true],
            'ints|string 2' => [Tuple::from([1, 'foo']), ['string|int', 'string|?int'], true],
            'ints|string 3' => [Tuple::from(['string', null]), ['string|int', 'string|?int'], true],
            'ints|string 4' => [Tuple::from([null, 2]), ['string|int', 'string|?int'], false],
            'ints|string 5' => [Tuple::from([null, 'foo']), ['string|int', 'string|?int'], false],
            'int|array' => [Tuple::from([1, ['foo']]), ['int|array', 'int|array'], true],
        ];
    }

    /** @dataProvider provideInvalidTypes */
    public function testShouldNotMatchWhenAskToMatchNotEnoughTypes(array $types): void
    {
        $tuple = Tuple::of('foo', 'bar');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tuples has always at least 2 values. It would always be false by giving less then 2 types.');

        $tuple->matchTypes($types);
    }

    public function provideInvalidTypes(): array
    {
        return [
            // invalid types
            'empty' => [[]],
            'one int' => [['int']],
            'one string' => [['string']],
        ];
    }

    public function testShouldUnpackTuple(): void
    {
        $result = sprintf('Title: %s | Value: %s', ...Tuple::of('foo', 'bar'));

        $this->assertSame('Title: foo | Value: bar', $result);
    }

    public function testShouldUnpackTuples(): void
    {
        $format = function (string $title, string $value): string {
            return sprintf('%s: %s', $title, $value);
        };

        $values = [Tuple::of('title', 'value'), Tuple::of('type', 'great')];

        $result = array_map(function (Tuple $tuple) use ($format) {
            return $format(...$tuple);
        }, $values);

        $this->assertSame(['title: value', 'type: great'], $result);
    }

    /**
     * @dataProvider provideTupleMerge
     */
    public function testShouldMergeTuple(ITuple $base, array $additional, ITuple $expected): void
    {
        $result = Tuple::merge($base, ...$additional);

        $this->assertSame($expected->toArray(), $result->toArray());
    }

    public function provideTupleMerge(): array
    {
        return [
            // base tuple, additional, expected
            '(1, 2) + 3 - ints' => [Tuple::of(1, 2), [3], Tuple::of(1, 2, 3)],
            '(1, 2) + [3, 4] - array' => [Tuple::of(1, 2), [[3, 4]], Tuple::of(1, 2, [3, 4])],
            '(1, 2) + (3, 4) - strings' => [
                Tuple::of('1', '2'),
                [Tuple::of('3', '4')],
                Tuple::of('1', '2', '3', '4'),
            ],
            '(1, 2) + (3, 4) + 5 + [6, 7, 8] - mixed' => [
                Tuple::of('1', '2'),
                [Tuple::of('3', '4'), 5, [6, 7, 8]],
                Tuple::of('1', '2', '3', '4', 5, [6, 7, 8]),
            ],
            '(foo, bar) + boo - string' => [
                Tuple::parse('(foo, bar)'),
                ['boo'],
                Tuple::of('foo', 'bar', 'boo'),
            ],
            '(1, 2, 3) + four - mixed' => [
                Tuple::parse('(1, 2, 3)'),
                ['four'],
                Tuple::of(1, 2, 3, 'four'),
            ],
            '(1, 2) + (3, 4) + (5, 6) - ints' => [
                Tuple::parse('(1, 2)'),
                [Tuple::of(3, 4), Tuple::from([5, 6])],
                Tuple::of(1, 2, 3, 4, 5, 6),
            ],
        ];
    }

    /**
     * @dataProvider provideTupleMergeMatch
     */
    public function testShouldMatchTupleAfterMerging(
        ITuple $base,
        array $additional,
        array $expectedTypes,
        ITuple $expected
    ): void {
        $result = Tuple::mergeMatch($expectedTypes, $base, ...$additional);

        $this->assertSame($expected->toArray(), $result->toArray());
    }

    public function provideTupleMergeMatch(): array
    {
        return [
            // base tuple, additional, expected types, expected message
            '(1, 2, 3) + "4" = (int, int, int, string)' => [
                Tuple::parse('(1, 2, 3)'),
                ['4'],
                ['int', 'int', 'int', 'string'],
                Tuple::of(1, 2, 3, '4'),
            ],
            '(1, 2) + 3 = (int, int, int)' => [
                Tuple::parse('(1, 2)'),
                [3],
                ['int', 'int', 'int'],
                Tuple::of(1, 2, 3),
            ],
            '([1, 2], 3) + 4 = (array, int, int)' => [
                Tuple::parse('([1; 2], 3)'),
                [4],
                ['array', 'int', 'int'],
                Tuple::of([1, 2], 3, 4),
            ],
        ];
    }

    /**
     * @dataProvider provideTupleMergeDoNotMatch
     */
    public function testShouldNotMatchTupleAfterMerging(
        ITuple $base,
        array $additional,
        array $expectedTypes,
        string $expectedMessage
    ): void {
        $this->expectException(TupleMatchException::class);
        $this->expectExceptionMessage($expectedMessage);

        Tuple::mergeMatch($expectedTypes, $base, ...$additional);
    }

    public function provideTupleMergeDoNotMatch(): array
    {
        return [
            // base tuple, additional, expected types, expected message
            '(1, 2, 3) + "4" <> (int, int)' => [
                Tuple::parse('(1, 2, 3)'),
                ['4'],
                ['int', 'int'],
                'Given tuple does NOT match expected types (int, int) - got (int, int, int, string).',
            ],
            '(1, 2) + 3 <> (int, string)' => [
                Tuple::parse('(1, 2)'),
                [3],
                ['int', 'string'],
                'Given tuple does NOT match expected types (int, string) - got (int, int, int).',
                '(int, string) expected but got (int, int, int)',
            ],
        ];
    }

    /**
     * @dataProvider provideTupleForUrl
     */
    public function testShouldFormatTupleForUrl(ITuple $tuple, string $expected): void
    {
        $result = $tuple->toStringForUrl();

        $this->assertSame($expected, $result);

        $parsed = Tuple::parse($result);
        $this->assertSame($tuple->toArray(), $parsed->toArray());
    }

    public function provideTupleForUrl(): array
    {
        return [
            // tuple, expected
            'simple strings' => [Tuple::from(['foo', 'bar', 'boo']), '(foo,bar,boo)'],
            'ints' => [Tuple::from([1, 2, 3]), '(1,2,3)'],
            'floats' => [Tuple::from([1.2, 3.4]), '(1.2,3.4)'],
            '?bools' => [Tuple::from([true, false, null]), '(true,false,null)'],
            'arrays' => [Tuple::from(['one', [2, 3], 4]), '(one,[2;3],4)'],
            'string with numbers, underscores, dashesh, dots, space, ...' => [
                Tuple::from(['a_b-c.d', 'a1-b2', 'x y']),
                '(a_b-c.d,a1-b2,x y)',
            ],
            'complex strings' => [
                Tuple::from(['foo, bar & boo', 'Another "complexString"', 'simple']),
                '("foo, bar & boo","Another "complexString"",simple)',
            ],
            'complex strings in array' => [
                Tuple::from([['foo bar & boo', 'Another \'complexString\''], 'simple']),
                '(["foo bar & boo";"Another \'complexString\'"],simple)',
            ],
        ];
    }
}
