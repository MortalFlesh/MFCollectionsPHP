<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\OutOfBoundsException;
use MF\Collection\Exception\OutOfRangeException;
use MF\Collection\Fixtures\DummySeq;
use MF\Collection\Fixtures\SimpleEntity;

/**
 * @group sequence
 */
class SeqTest extends AbstractTestCase
{
    public function testShouldCreateSeqOfValue(): void
    {
        $result = Seq::of(1, 2, 3)->toArray();

        $this->assertSame([1, 2, 3], $result);

        $a = [1, 2];
        $b = [3, 4];
        $result = Seq::of(...$a, ...$b)->toArray();

        $this->assertSame([1, 2, 3, 4], $result);
    }

    /** @dataProvider provideSeq */
    public function testShouldCreateSeqFrom(iterable $input, array $expectedKeys, array $expectedValues): void
    {
        $seq = Seq::from($input);

        $keys = [];
        $values = [];
        foreach ($seq as $k => $v) {
            $keys[] = $k;
            $values[] = $v;
        }

        $this->assertSame(
            $expectedKeys,
            $keys,
            sprintf(
                'Keys [%s] are not same as expected [%s].',
                implode(', ', $keys),
                implode(', ', $expectedKeys),
            ),
        );
        $this->assertSame(
            $expectedValues,
            $values,
            sprintf(
                'Values [%s] are not same as expected [%s].',
                implode(', ', $values),
                implode(', ', $expectedValues),
            ),
        );
    }

    public static function provideSeq(): array
    {
        return [
            // input, expectedKeys, expectedValues
            'array' => [[1, 2, 3], [0, 1, 2], [1, 2, 3]],
            'range' => [range(1, 5), [0, 1, 2, 3, 4], [1, 2, 3, 4, 5]],
            'List' => [ListCollection::from([1, 2, 3]), [0, 1, 2], [1, 2, 3]],
        ];
    }

    public function testShouldMakeSeqForRangeAndDoSquareOnInfiniteSeq(): void
    {
        $expectedValues = [1, 4, 9];

        $seq = Seq::forDo('1..Inf', fn ($i) => $i * $i)
            ->take(3);

        $values = [];
        foreach ($seq as $i) {
            $values[] = $i;
        }

        $this->assertSame($expectedValues, $values);
    }

    public function testShouldMakeSeqForRangeAndDoSquareOnInfiniteSeqSkippingFirstItems(): void
    {
        $inf = Seq::forDo('1..7', fn ($i) => $i * $i); // 1, 4, 9, ...
        $skip2 = $inf->skip(2);
        $take3 = $skip2->take(3);

        $this->assertSame([9, 16, 25, 36, 49], $skip2->toArray());
        $this->assertSame([9, 16, 25], $take3->toArray());
    }

    public function testShouldTakeItemsFromInfiniteSeqAndSkipThemToReturnEmptySeq(): void
    {
        $array = Seq::range('1..Inf')
            ->take(3)
            ->skip(3)
            ->toArray();

        $this->assertSame([], $array);
    }

    public function testShouldSkipItemsAndThenTakeSome(): void
    {
        $array = Seq::range('1..Inf')
            ->skip(3)
            ->take(3)
            ->toArray();

        $this->assertSame([4, 5, 6], $array);
    }

    public function testShouldSkipMultipleTimesAndThenTakeSome(): void
    {
        $array = Seq::range('1..Inf')
            ->skip(5)
            ->skip(5)
            ->skipWhile(fn ($i) => $i % 2 === 0)
            ->take(5)
            ->toArray();

        $this->assertSame([11, 12, 13, 14, 15], $array);
    }

    public function testShouldGenerateSeqForRangeAndDoSquare(): void
    {
        $expectedValues = [1, 4, 9];

        $seq = Seq::forDo('1..3', function ($i) {
            yield $i * $i;
        });

        $values = [];
        foreach ($seq as $i) {
            $values[] = $i;
        }

        $this->assertSame($expectedValues, $values);
    }

    public function testShouldGenerateSeqFor(): void
    {
        $result = Seq::forDo('0..10', fn () => yield 1)->take(5)->toArray();

        $this->assertSame([1, 1, 1, 1, 1], $result);
    }

    public function testShouldGenerateSeqForEvenNumbers(): void
    {
        $result = Seq::forDo('1 .. 10', function ($i) {
            if ($i % 2 === 0) {
                yield $i;
            }
        })->toArray();

        $this->assertSame([2, 4, 6, 8, 10], $result);
    }

    public function testShouldGenerateSeqForGrid(): void
    {
        /**
         * F#:
         * let (height, width) = (10, 10)
         * seq { for row in 0 .. width - 1 do
         *          for col in 0 .. height - 1 do
         *              yield (row, col, row*width + col)
         * }
         */
        [$height, $width] = [5, 5];
        $result = Seq::init(function () use ($height, $width) {
            foreach (range(0, $width - 1) as $row) {
                foreach (range(0, $height - 1) as $col) {
                    yield [$row, $col, $row * $width + $col];
                }
            }
        })->toArray();

        $expected = [
            [0, 0, 0],
            [0, 1, 1],
            [0, 2, 2],
            [0, 3, 3],
            [0, 4, 4],
            [1, 0, 5],
            [1, 1, 6],
            [1, 2, 7],
            [1, 3, 8],
            [1, 4, 9],
            [2, 0, 10],
            [2, 1, 11],
            [2, 2, 12],
            [2, 3, 13],
            [2, 4, 14],
            [3, 0, 15],
            [3, 1, 16],
            [3, 2, 17],
            [3, 3, 18],
            [3, 4, 19],
            [4, 0, 20],
            [4, 1, 21],
            [4, 2, 22],
            [4, 3, 23],
            [4, 4, 24],
        ];

        $this->assertSame($expected, $result);
    }

    public function testShouldGenerateSeqForCube(): void
    {
        $size = 2;
        $seq = Seq::init(function () use ($size) {
            foreach (range(1, 6) as $side) {
                foreach (range(0, $size - 1) as $row) {
                    foreach (range(0, $size - 1) as $column) {
                        yield $side => [$row, $column];
                    }
                }
            }
        });

        $result = [];
        foreach ($seq as $sideNumber => $side) {
            $result[$sideNumber][] = $side;
        }

        $expected = [
            1 => [
                [0, 0],
                [0, 1],
                [1, 0],
                [1, 1],
            ],
            2 => [
                [0, 0],
                [0, 1],
                [1, 0],
                [1, 1],
            ],
            3 => [
                [0, 0],
                [0, 1],
                [1, 0],
                [1, 1],
            ],
            4 => [
                [0, 0],
                [0, 1],
                [1, 0],
                [1, 1],
            ],
            5 => [
                [0, 0],
                [0, 1],
                [1, 0],
                [1, 1],
            ],
            6 => [
                [0, 0],
                [0, 1],
                [1, 0],
                [1, 1],
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function testShouldSquareInfiniteWhile(): void
    {
        $result = Seq::infinite()
            ->filter(fn ($i) => $i % 2 === 0)
            ->map(fn ($i) => $i * $i)
            ->takeWhile(fn ($i) => $i < 25)
            ->toArray();

        $this->assertSame([4, 16], $result);
    }

    public function testShouldSquareInfiniteWhileSkippingWhile(): void
    {
        $result = Seq::infinite()               // 1, 2, 3, ...
            ->filter(fn ($i) => $i % 2 === 0)   // 2, 4, 6, ...
            ->skipWhile(fn ($i) => $i < 10)     // 10, 12, 14, ...
            ->map(fn ($i) => $i * $i)           // 100, 144, 169, ...
            ->takeWhile(fn ($i) => $i < 150)    // 100, 144
            ->toArray();

        $this->assertSame([100, 144], $result);
    }

    public function testShouldSquareInfiniteWhileAndThenMapIt(): void
    {
        $result = Seq::infinite()
            ->filter(fn ($i) => $i % 2 === 0)   // 2, 4, 6, ... Inf
            ->map(fn ($i) => $i * $i)           // 4, 16, 36 ... Inf
            ->takeWhile(fn ($i) => $i < 25)     // 4, 16
            ->map(fn ($i) => sqrt($i))          // 2.0, 4.0
            ->map(fn ($i) => (int) $i)          // 2, 4
            ->filter(fn ($i) => $i > 2)         // 4
            ->toArray();

        $this->assertSame([4], $result);
    }

    public function testShouldGenerateSeqForRangeAndDoItLarger(): void
    {
        $expectedValues = [1, 2, 2, 3, 3];

        $seq = Seq::forDo([1, 3], function ($i) {
            if ($i > 1) {
                yield $i;
            }

            yield $i;
        });

        $values = [];
        foreach ($seq as $i) {
            $values[] = $i;
        }

        $this->assertSame($expectedValues, $values);
    }

    public function testShouldGenerateSeqForRange(): void
    {
        $expectedValues = [1, 2, 3];

        $seq = Seq::forDo([1, 3], fn ($i) => $i);

        $values = [];
        foreach ($seq as $i) {
            $values[] = $i;
        }

        $this->assertSame($expectedValues, $values);
    }

    public function testShouldCreateSeq(): void
    {
        $expectedValues = [1, 2, 3];

        $seq = Seq::create([1, 2, 3], fn ($i) => $i);

        $values = [];
        foreach ($seq as $i) {
            $values[] = $i;
        }

        $this->assertSame($expectedValues, $values);
    }

    public function testShouldGenerateSeqForRangeAndDoItLarger2(): void
    {
        $values = Seq::forDo([1, 3], function ($i) {
            if ($i > 1) {
                yield $i;
            }

            yield $i;
        })->toArray();

        $this->assertSame([1, 2, 2, 3, 3], $values);
    }

    public function testShouldGenerateSeqForRangeAndDoItLargerAndTakeOnly2Items(): void
    {
        $expectedValues = [1, 2, 2];

        $values = [];
        $seq = Seq::forDo([1, 3], function ($i) {
            if ($i > 1) {
                yield $i;
            }

            yield $i;
        });

        foreach ($seq->take(3) as $i) {
            $values[] = $i;
        }

        $this->assertSame($expectedValues, $values);
    }

    /** @dataProvider provideRange */
    public function testShouldGenerateRange(mixed $range, array $expected): void
    {
        $result = Seq::range($range)->toArray();

        $this->assertSame($expected, $result);
    }

    public static function provideRange(): array
    {
        return [
            // range, expected
            'range by array' => [[1, 5], [1, 2, 3, 4, 5]],
            'range by array with step' => [[0, 2, 10], [0, 2, 4, 6, 8, 10]],
            'range by string with spaces' => [' 0 .. 3 ', [0, 1, 2, 3]],
            'range by string' => ['0..3', [0, 1, 2, 3]],
            'range by string with step' => ['1..3..10', [1, 4, 7, 10]],
        ];
    }

    public function testShouldGenerateSimpleFibonacci(): void
    {
        $simpleFib = Seq::init(function () {
            yield 2;
            yield 3;
            yield 5;
            yield 8;
            yield 13;
        })->toArray();

        $this->assertSame([2, 3, 5, 8, 13], $simpleFib);
    }

    public function testShouldGenerateComputedFibonacci(): void
    {
        $computedFib = Seq::unfold(
            function (array $state) {
                [$first, $second] = $state;

                return $second > 1000
                    ? null
                    : [$first + $second, [$second, $first + $second]];
            },
            [1, 1],
        )->toArray();

        $this->assertSame([2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610, 987, 1597], $computedFib);
    }

    public function testShouldGenerateComputedFibonacciWithLimit(): void
    {
        $computedFib = Seq::unfold(
            function (array $state) {
                [$first, $second] = $state;

                return [$first + $second, [$second, $first + $second]];
            },
            [1, 1],
        )
            ->takeWhile(function ($i) {
                return $i < 500;
            })
            ->toArray();

        $this->assertSame([2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377], $computedFib);
    }

    public function testShouldGenerateSeqWithLimit(): void
    {
        $values = Seq::from([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
            ->takeWhile(fn ($i) => $i < 5)
            ->toArray();

        $this->assertSame([1, 2, 3, 4], $values);
    }

    public function testShouldUnfoldNumbers(): void
    {
        $seq = Seq::unfold(
            function (int $state): ?array {
                return $state > 10
                    ? null
                    : [$state, $state + 1];
            },
            0,
        );
        $result = [];
        foreach ($seq as $i) {
            $result[] = $i;
        }

        $this->assertSame([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $result);
    }

    public function testShouldGenerateItemsFromNumbers(): void
    {
        $result = Seq::init(function () {
            yield 1;
            yield 2;
            yield 3;
        })
            ->map(function (int $i): string {
                return sprintf('item_%d', $i);
            })
            ->toArray();

        $this->assertSame(['item_1', 'item_2', 'item_3'], $result);
    }

    public function testShouldMapAndFilterRange(): void
    {
        $result = Seq::range('1..100')
            ->take(10)
            ->filter(fn ($i) => $i % 2 === 0)// 2, 4, 6, 8, 10
            ->map(fn ($i) => $i * $i)// 4, 16, 36, 64, 100
            ->map(fn ($i) => $i . '_item')// '4_item', '16_item', '36_item', '64_item', '100_item'
            ->filter(fn ($i) => $i[0] !== '1')// '4_item', '36_item', '64_item'
            ->take(2)// '4_item', '36_item'
            ->toArray();

        $this->assertSame(['4_item', '36_item'], $result);
    }

    public function testShouldGenerateLimitedNumbers(): void
    {
        $result = Seq::init(function () {
            yield 1;
            yield 2;
            yield 3;
        })
            ->map(fn ($i) => 'item_' . $i)
            ->take(2)
            ->toArray();

        $this->assertSame(['item_1', 'item_2'], $result);
    }

    public function testShouldTakeSomeItemsFromRange(): void
    {
        $result = Seq::range([1, Seq::INFINITE])
            ->takeUpTo(2)
            ->toArray();

        $this->assertSame([1, 2], $result);
    }

    public function testShouldTakeFirstTwoItemsOfGeneratedSeq(): void
    {
        $result = Seq::init(function () {
            foreach ([1, 2, 3] as $i) {
                yield $i;

                if ($i === 1) {
                    yield $i + 1;
                }
            }
        })
            ->take(2)
            ->toArray();

        $this->assertSame([1, 2], $result);
    }

    public function testShouldThrowOutOfRangeExceptionOnTryingToTakeToManyItemsFromGenerating(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Seq does not have 5 items to take, it only has 3 items.');

        Seq::init(function () {
            foreach ([1, 2, 3] as $i) {
                yield $i;
            }
        })
            ->take(5)
            ->toArray();
    }

    public function testShouldTakeAtMostFewItemsFromGenerating(): void
    {
        $result = Seq::init(function () {
            foreach ([1, 2, 3] as $i) {
                yield $i;
            }
        })
            ->takeUpTo(5)
            ->toArray();

        $this->assertCount(3, $result);
    }

    public function testShouldThrowOutOfRangeExceptionOnTryingToTakeToManyItemsFromRange(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Seq does not have 5 items to take, it only has 2 items.');

        Seq::range([1, 2])
            ->take(5)
            ->toArray();
    }

    public function testShouldGenerateInfiniteRangeUntilLimit(): void
    {
        $result = Seq::range([1, 'Inf'])
            ->takeWhile(function ($i): bool {
                return $i <= 100;
            })
            ->toArray();

        $this->assertCount(100, $result);
    }

    public function testShouldGenerateInfiniteRangeByStringDefinitionUntilLimit(): void
    {
        $result = Seq::range('1..Inf')
            ->takeWhile(fn ($i) => $i < 100)
            ->toArray();

        $this->assertCount(99, $result);
    }

    public function testShouldThrowOutOfRangeOnTakingFromValues(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Seq does not have 5 items to take, it only has 3 items.');

        Seq::from([1, 2, 3])->take(5)->toArray();
    }

    /** @dataProvider provideIsEmpty */
    public function testShouldCheckIfSeqIfEmpty(ISeq $seq, bool $isEmpty): void
    {
        $result = $seq->isEmpty();

        $this->assertSame($isEmpty, $result);
    }

    public static function provideIsEmpty(): array
    {
        return [
            'empty' => [Seq::createEmpty(), true],
            'empty from' => [Seq::from([]), true],
            'not empty infinite' => [Seq::infinite(), false],
            'not empty init' => [Seq::init(fn () => yield 1), false],
            'not empty init with generator' => [
                Seq::init(function () {
                    foreach ([1, 2, 3] as $i) {
                        yield $i;
                    }
                }),
                false,
            ],
            'not empty from' => [Seq::from([1, 2]), false],
            'not empty range' => [Seq::range('1..2..10'), false],
            'not empty forDo' => [
                Seq::forDo([1, 2, 3], function ($i) {
                    yield $i * 2;
                }),
                false,
            ],
            'not empty of' => [Seq::of(1, 2), false],
            'empty after clear' => [Seq::of(1, 2)->clear(), true],
            'empty after take 0' => [Seq::range('1..Inf')->take(0), true],
            'empty after takeUpTo 0' => [Seq::from([1, 2])->takeUpTo(0), true],
            'empty after takeWhile' => [Seq::infinite()->takeWhile(fn () => false), true],
            'empty after filterAll' => [Seq::range('1..10..100')->filter(fn () => false), true],
        ];
    }

    /** @dataProvider provideCount */
    public function testShouldCountSeq(ISeq $seq, int $expected): void
    {
        $count = $seq->count();

        $this->assertSame($expected, $count);
    }

    public static function provideCount(): array
    {
        return [
            // seq, expectedCount
            'count' => [Seq::createEmpty(), 0],
            'count create' => [Seq::create([], fn ($i) => $i), 0],
            'count from' => [Seq::from([]), 0],
            'count from 2' => [Seq::from([1, 2]), 2],
            'count init' => [Seq::init(fn () => yield 1), 1],
            'count init with generator' => [
                Seq::init(function () {
                    foreach ([1, 2, 3] as $i) {
                        yield $i;
                    }
                }),
                3,
            ],
            'count range' => [Seq::range('1..2..10'), 5],
            'count forDo' => [
                Seq::forDo([1, 5], function ($i) {
                    yield $i * 2;
                }),
                5,
            ],
            'count of' => [Seq::of(1, 2), 2],
            'count after clear' => [Seq::of(1, 2)->clear(), 0],
            'count after take 0' => [Seq::range('1..Inf')->take(0), 0],
            'count after takeUpTo 0' => [Seq::from([1, 2])->takeUpTo(0), 0],
            'count after takeWhile' => [Seq::infinite()->takeWhile(fn () => false), 0],
            'count forDo on infinite while' => [
                Seq::forDo('0..10..Inf', fn ($i) => yield $i => 'item_' . $i)->takeWhile(fn ($i, $k = null) => $k < 100),
                10,
            ],
            'count forDo on infinite' => [
                Seq::forDo('0..10..Inf', fn ($i) => yield $i)->take(10),
                10,
            ],
            'large - count range' => [
                Seq::range('1..1000'),
                1000,
            ],
            'large - count iterable' => [
                Seq::from(range(1, 1000)),
                1000,
            ],
            'large - count forDo on infinite while' => [
                Seq::forDo('0..10..Inf', fn ($i) => yield $i => 'item_' . $i)->takeWhile(fn ($i, $k = null) => $k < 10000),
                1000,
            ],
            'large - count forDo on infinite' => [
                Seq::forDo('0..10..Inf', fn ($i) => yield 'item_' . $i)->take(1000),
                1000,
            ],
            'large - count create' => [
                Seq::create(range(1, 1000), fn ($i) => yield 'item_' . $i),
                1000,
            ],
            'seq of seq' => [
                Seq::init(Seq::range('1..10')),
                10,
            ],
        ];
    }

    public function testShouldThrowExceptionOnCountingInfiniteSeq(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('It is not possible to count infinite seq.');

        Seq::infinite()->count();
    }

    public function testShouldReduceSeq(): void
    {
        $sumOfSquaredOddNumbersFrom1to10 = Seq::range('1..10')
            ->filter(fn ($i) => $i % 2 === 1)// 1, 3, 5, 7, 9
            ->map(fn ($i) => $i * $i)// 1, 9, 25, 49, 81
            ->reduce(fn ($t, $i) => $t + $i, 0);

        $this->assertSame(165, $sumOfSquaredOddNumbersFrom1to10);
    }

    /** @dataProvider provideContains */
    public function testShouldContainsValue(ISeq $seq, mixed $value, mixed $expected): void
    {
        $result = $seq->contains($value);

        $this->assertSame($expected, $result);
    }

    public static function provideContains(): array
    {
        return [
            /// seq, value, expected
            'empty' => [Seq::createEmpty(), true, false],
            'in range' => [Seq::range('1..10'), 5, true],
            'not in range' => [Seq::range('1..10'), 11, false],
            'in infinite' => [Seq::range('0..10..Inf'), 100, true],
            'not in filtered range' => [Seq::range('0..10..100')->filter(fn ($i) => $i !== 100), 100, false],
            'not in limited range' => [Seq::range('0..10..100')->take(2), 30, false],
            'in static' => [Seq::from(['hello']), 'hello', true],
        ];
    }

    /** @dataProvider provideContains */
    public function testShouldContainsValueBy(ISeq $seq, mixed $value, mixed $expected): void
    {
        $result = $seq->containsBy($this->findByValue($value));

        $this->assertSame($expected, $result);
    }

    public function testShouldContainsValueByArrowFunction(): void
    {
        $seq = Seq::from(['1', '2']);
        $contains2NonStrict = $seq->containsBy(fn ($v) => $v == 2);
        $contains3NonStrict = $seq->containsBy(fn ($v) => $v == 3);

        $this->assertTrue($contains2NonStrict);
        $this->assertFalse($contains3NonStrict);
    }

    public function testShouldCheckIfNumberFromRangeIsOdd(): void
    {
        $result = Seq::range('3..2..Inf')
            ->filter(fn ($i) => $i % 2 === 1)
            ->take(5)
            ->toArray();

        $this->assertSame([3, 5, 7, 9, 11], $result);
    }

    public function testShouldCollectIntSequence(): void
    {
        $entity = new class([1, 2, 3]) {
            public function __construct(private readonly array $data)
            {
            }

            public function toArray(): array
            {
                return $this->data;
            }
        };

        $result = Seq::of($entity)
            ->collect((fn ($s) => $s->toArray()))
            ->toArray();

        $this->assertSame([1, 2, 3], $result);
    }

    public function testShouldCollectSequence(): void
    {
        $data = [1, 2, 3];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = Seq::init(function () use ($data): iterable {
            yield from $data;
        })
            ->collect(function (int $item) use ($subData): iterable {
                return $subData[$item];
            })
            ->reduce(function (string $word, string $subItem): string {
                return $word . $subItem;
            }, 'Word: ');

        $this->assertSame('Word: abcdefg', $word);
    }

    public function testShouldMapSequenceCollectAndMapAgain(): void
    {
        $data = ['1 ', ' 2 ', '3'];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = Seq::init(function () use ($data): iterable {
            yield from $data;
        })
            ->map(fn ($i) => (int) $i)
            ->collect(function (int $item) use ($subData): iterable {
                return $subData[$item];
            })
            ->filter(fn ($l) => $l < 'f')
            ->map(fn ($l) => $l . ' ')
            ->reduce(function (string $word, string $subItem): string {
                return $word . $subItem;
            }, 'Word: ');

        $this->assertSame('Word: a b c d e ', $word);
    }

    public function testShouldConcatIntSequence(): void
    {
        $result = Seq::from([[1, 2, 3], [4, 5, 6], 7, 8])
            ->concat()
            ->toArray();

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8], $result);
    }

    public function testShouldConcatSequence(): void
    {
        $data = [1, 2, 3];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = Seq::init(function () use ($data): iterable {
            yield from $data;
        })
            ->map(fn (int $item): iterable => $subData[$item])
            ->concat()
            ->reduce(fn (string $word, string $subItem): string => $word . $subItem, 'Word: ');

        $this->assertSame('Word: abcdefg', $word);
    }

    public function testShouldMapSequenceConcatAndMapAgain(): void
    {
        $data = ['1 ', ' 2 ', '3'];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = Seq::init(function () use ($data): iterable {
            yield from $data;
        })
            ->map(fn ($i) => (int) $i)
            ->map(fn (int $item): iterable => $subData[$item])
            ->concat()
            ->filter(fn ($l) => $l < 'f')
            ->map(fn ($l) => $l . ' ')
            ->reduce(fn (string $word, string $subItem): string => $word . $subItem, 'Word: ');

        $this->assertSame('Word: a b c d e ', $word);
    }

    public function testShouldCollectInfiniteSeq(): void
    {
        $result = Seq::infinite()
            ->collect(function ($i) {
                return [$i, $i];
            })
            ->take(3)
            ->toArray();

        $this->assertSame([1, 1, 2], $result);
    }

    public function testShouldImplodeSeq(): void
    {
        $expected = '1, 2, 3';

        $result = Seq::init(function () {
            yield 1;
            yield from [2, 3];
        })
            ->implode(', ');

        $this->assertSame($expected, $result);
    }

    public function testShouldCreateSequenceByArrowFunctionWithGenerator(): void
    {
        $result = Seq::init(fn () => yield from range(1, 5))->toArray();

        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    public function testShouldBeEmptyAfterClear(): void
    {
        $empty = Seq::createEmpty();
        $withItems = Seq::range('0..10');

        $this->assertNotEquals($empty, $withItems);
        $this->assertEquals($empty, $withItems->clear());
    }

    public function testShouldAccessEachItemInInfiniteSeqWhileConditionIsMet(): void
    {
        $data = [];

        Seq::range('0 .. Inf')
            ->map(fn ($i) => $i * 2)
            ->takeWhile(fn ($i) => $i < 10)
            ->each(function ($i) use (&$data): void {
                $data[] = $i;
            });

        $this->assertSame([0, 2, 4, 6, 8], $data);
    }

    public function testShouldIterateTheSeqMoreThanOnce(): void
    {
        $data = [];

        $infiniteRange = Seq::range('0 .. Inf');
        $infiniteRangeDoubled = $infiniteRange->map(fn ($i) => $i * 2);
        $infiniteRangeTripled = $infiniteRange->map(fn ($i) => $i * 3);

        foreach ($infiniteRangeDoubled->take(5) as $i) {
            $this->ignore($i);
        }
        foreach ($infiniteRangeDoubled->take(5) as $i) {
            $this->ignore($i);
        }

        $infiniteRangeDoubled
            ->takeWhile(fn ($i) => $i < 10)
            ->each(function ($i) use (&$data): void {
                $data[] = $i;
            });

        $infiniteRangeTripled
            ->takeWhile(fn ($i) => $i < 10)
            ->each(function ($i) use (&$data): void {
                $data[] = $i;
            });

        $this->assertSame([0, 2, 4, 6, 8, 0, 3, 6, 9], $data);
    }

    public function testShouldTransformSeqToList(): void
    {
        $list = Seq::forDo('1..5', fn ($i) => $i)->toList();
        $this->assertSame([1, 2, 3, 4, 5], $list->toArray());
    }

    public function testShouldTransformInfiniteSeqToList(): void
    {
        $list = Seq::forDo('1..Inf', fn ($i) => $i)->take(5)->toList();
        $this->assertSame([1, 2, 3, 4, 5], $list->toArray());
    }

    public function testShouldNotTransformInfiniteSeqToList(): void
    {
        $this->expectException(OutOfBoundsException::class);

        Seq::forDo('1..Inf', fn ($i) => $i)->toList();
    }

    public function testShouldCheckPredicateForAllItems(): void
    {
        $seq = Seq::range('1..5');

        $this->assertTrue($seq->forAll(is_int(...)));
        $this->assertFalse($seq->forAll(is_string(...)));
    }

    public function testShouldNotCheckPredicateInInfiniteSequence(): void
    {
        $seq = Seq::infinite();

        $this->assertFalse($seq->forAll(is_string(...)));
    }

    public function testShouldSortSeq(): void
    {
        $seq = Seq::from([1, 3, 2, 5, 4, 0]);

        $this->assertSame([0, 1, 2, 3, 4, 5], $seq->sort()->toArray());
    }

    public function testShouldNotSortInfiniteSeq(): void
    {
        $this->expectException(OutOfBoundsException::class);

        Seq::infinite()->sort();
    }

    public function testShouldSortDescendingSeq(): void
    {
        $seq = Seq::from([1, 3, 2, 5, 4, 0]);

        $this->assertSame([5, 4, 3, 2, 1, 0], $seq->sortDescending()->toArray());
    }

    public function testShouldSortSeqAscAndDesc(): void
    {
        $seq = Seq::from([1, 3, 2, 5, 4, 0]);

        $this->assertSame(
            $seq->sort()->toArray(),
            $seq->sortDescending()->reverse()->toArray(),
        );
    }

    public function testShouldSortSeqByCallback(): void
    {
        $seq = Seq::from(['a', 'ccc', '😍', 'eeeee', 'bb'])
            ->sortBy(strlen(...));

        $this->assertSame(['a', 'bb', 'ccc', '😍', 'eeeee'], $seq->toArray());
    }

    public function testShouldNotSortDescendingInfiniteSeq(): void
    {
        $this->expectException(OutOfBoundsException::class);

        Seq::infinite()->sortDescending();
    }

    public function testShouldReverseGeneratedSequence(): void
    {
        $seq = Seq::forDo('1..2..Inf', fn ($i) => $i)
            ->takeWhile(fn ($i) => $i < 10)
            ->reverse();

        $this->assertSame([9, 7, 5, 3, 1], $seq->toArray());
    }

    /** @dataProvider provideChunkBySize */
    public function testShouldChunkSequenceBySize(int $size, array $data, array $expected): void
    {
        $chunks = Seq::from($data)
            ->chunkBySize($size)
            ->toArray();

        $this->assertSame($expected, $chunks);
    }

    public static function provideChunkBySize(): array
    {
        return [
            // size, data, expected
            '1' => [1, [1, 2, 3], [[1], [2], [3]]],
            '2' => [2, [1, 2, 3], [[1, 2], [3]]],
            '3' => [3, [1, 2, 3], [[1, 2, 3]]],
        ];
    }

    public function testShouldChunkInfiniteSeq(): void
    {
        $result = Seq::infinite()
            ->chunkBySize(5)
            ->skip(1)
            ->take(2)
            ->reverse()
            ->concat()
            ->toArray();

        $this->assertSame([11, 12, 13, 14, 15, 6, 7, 8, 9, 10], $result);
    }

    /** @dataProvider provideSplitData */
    public function testShouldSplitSequenceIntoMultipleSequences(int $count, array $data, array $expected): void
    {
        $result = Seq::from($data)
            ->splitInto($count)
            ->toArray();

        $this->assertSame($expected, $result);
    }

    public static function provideSplitData(): array
    {
        return [
            // count, data, expected
            'count 1' => [1, [1, 2, 3], [[1, 2, 3]]],
            'count 2' => [2, [1, 2, 3], [[1, 2], [3]]],
            'count 2 - even count' => [2, [1, 2, 3, 4], [[1, 2], [3, 4]]],
            'count 3' => [3, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [[1, 2, 3, 4], [5, 6, 7], [8, 9, 10]]],
            'count 4' => [4, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [[1, 2, 3], [4, 5, 6], [7, 8], [9, 10]]],
            'count 3 in 2 items' => [3, [1, 2], [[1], [2]]],
            'count 5 in 17 items' => [
                5,
                Seq::range('1..17')->toArray(),
                [
                    [1, 2, 3, 4],
                    [5, 6, 7, 8],
                    [9, 10, 11],
                    [12, 13, 14],
                    [15, 16, 17],
                ],
            ],
        ];
    }

    public function testShouldSortValuesBy(): void
    {
        $seq = Seq::from([
            'a',
            'eeeee',
            'bb',
            'ccc',
            'dd',
        ]);

        $sorted = $seq->sortBy(strlen(...));

        $this->assertNotEquals($seq, $sorted);
        $this->assertSame(['a', 'bb', 'dd', 'ccc', 'eeeee'], $sorted->toArray());
    }

    public function testShouldSortValuesAndReverseThem(): void
    {
        $seq = Seq::from([
            'a',
            'eeeee',
            'bb',
            'ccc',
            'dd',
        ]);

        $sorted = $seq->sortBy(strlen(...));
        $this->assertNotEquals($seq, $sorted);
        $this->assertSame(['a', 'bb', 'dd', 'ccc', 'eeeee'], $sorted->toArray());

        $sorted = $seq
            ->sortByDescending(strlen(...))
            ->reverse();
        $this->assertNotEquals($seq, $sorted);
        $this->assertSame(['a', 'dd', 'bb', 'ccc', 'eeeee'], $sorted->toArray());
    }

    public function testShouldKeepOnlyUniqueValues(): void
    {
        $seq = Seq::from([1, 3, 5, 7, 2, 3, 5, 4, 8, 2]);
        $unique = $seq->unique();

        $this->assertNotEquals($seq, $unique);
        $this->assertSame([1, 3, 5, 7, 2, 4, 8], $unique->toArray());
    }

    public function testShouldKeepOnlyUniqueValuesByCallback(): void
    {
        $seq = Seq::from([
            new SimpleEntity(1),
            new SimpleEntity(3),
            new SimpleEntity(2),
            new SimpleEntity(4),
            new SimpleEntity(2),
            new SimpleEntity(5),
            new SimpleEntity(1),
        ]);
        $unique = $seq->uniqueBy(fn (SimpleEntity $e) => $e->getId());

        $this->assertNotEquals($seq, $unique);
        $this->assertEquals(
            [
                new SimpleEntity(1),
                new SimpleEntity(3),
                new SimpleEntity(2),
                new SimpleEntity(4),
                new SimpleEntity(5),
            ],
            $unique->toArray(),
        );
    }

    public function testShouldMapObjectsToDifferentSequenceAndSumValues(): void
    {
        $seq = Seq::from([
            new SimpleEntity(1),
            new SimpleEntity(2),
            new SimpleEntity(3),
        ]);

        $seq = $seq
            ->filter(fn (SimpleEntity $v) => $v->getId() > 1)
            ->map(fn (SimpleEntity $v) => $v->getId());
        $sum = $seq->sum();

        $this->assertSame(5, $sum);
        $this->assertEquals([2, 3], $seq->toArray());
    }

    public function testShouldSumGenericSequenceOfSequenceCountsByCallback(): void
    {
        $seq1 = Seq::from([1, 2, 3]);
        $seq2 = Seq::from(['one', 'two']);

        $seq3 = Seq::of($seq1, $seq2);

        $this->assertEquals(5, $seq3->sumBy(count(...)));
    }

    public function testShouldAppendSequences(): void
    {
        $seq1 = Seq::of(1, 'string', 2.1);
        $this->assertEquals([1, 'string', 2.1], $seq1->toArray());

        $values = [1, 2];
        $values2 = ['three', 'four'];
        $seq2 = Seq::of(...$values, ...$values2);
        $this->assertEquals([1, 2, 'three', 'four'], $seq2->toArray());

        $seq3 = $seq1->append($seq2);
        $this->assertEquals([1, 'string', 2.1, 1, 2, 'three', 'four'], $seq3->toArray());
    }

    public function testShouldAppendDifferentSequences(): void
    {
        $seq1 = Seq::of(1, 2, 3);
        $seq2 = DummySeq::from([4, 5, 6]);

        $seq3 = $seq1->append($seq2);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $seq3->toArray());
    }

    public function testShouldCountValuesByCallback(): void
    {
        $seq = Seq::from([1, 3, 5, 7, 2, 3, 5, 4, 8, 2]);

        $counts = $seq->countBy(fn ($v) => $v % 2 === 0 ? 'even' : 'odd');

        $this->assertEquals(
            [
                new KVPair('odd', 6),
                new KVPair('even', 4),
            ],
            $counts->toArray(),
        );
    }

    public function testShouldGroupList(): void
    {
        $groupsSeq = Seq::from([1, 2, 3, 4, 5, 6, 7])
            ->groupBy(fn (int $i) => $i % 2 === 0 ? 'even' : 'odd')
            ->toArray();

        $expected = [
            'odd' => [1, 3, 5, 7],
            'even' => [2, 4, 6],
        ];

        $groups = [];
        foreach ($groupsSeq as $group) {
            $this->assertInstanceOf(KVPair::class, $group);

            $values = $group->getValue();
            $this->assertInstanceOf(ISeq::class, $values);

            $groups[$group->getKey()] = $values->toArray();
        }

        $this->assertEquals($expected, $groups);
    }

    public function testShouldFindMinInList(): void
    {
        $seq = Seq::from([2, 1, 3, 5]);

        $this->assertSame(1, $seq->min());
    }

    public function testShouldFindMaxInList(): void
    {
        $seq = Seq::from([2, 1, 3, 5]);

        $this->assertSame(5, $seq->max());
    }

    public function testShouldFindMinInListByCallback(): void
    {
        $seq = Seq::from([
            new SimpleEntity(1),
            new SimpleEntity(3),
            new SimpleEntity(2),
        ]);

        $this->assertEquals(new SimpleEntity(1), $seq->minBy(fn (SimpleEntity $e) => $e->getId()));
    }

    public function testShouldFindMaxInListByCallback(): void
    {
        $seq = Seq::from([
            new SimpleEntity(1),
            new SimpleEntity(3),
            new SimpleEntity(2),
        ]);

        $this->assertEquals(new SimpleEntity(3), $seq->maxBy(fn (SimpleEntity $e) => $e->getId()));
    }

    public function testShouldGenerateSequenceMultipleTimes(): void
    {
        $isGenerated = false;

        $seq = Seq::init(function () use (&$isGenerated) {
            $isGenerated = true;

            yield 1;
            yield 2;
            yield 3;
        });

        $this->assertFalse($isGenerated);
        $this->assertSame([1, 2, 3], $seq->toArray());
        $this->assertTrue($isGenerated);

        $isGenerated = false;
        $this->assertSame([1, 2, 3], $seq->toArray());
        $this->assertTrue($isGenerated);

        $isGenerated = false;
        foreach ($seq as $v) {
            $this->assertTrue($isGenerated);
        }

        $isGenerated = false;
        $seq2 = $seq
            ->map(fn ($i) => $i)
            ->filter(fn ($i) => $i % 2 === 0)
            ->append(Seq::from([4, 6]));
        $this->assertFalse($isGenerated);
        $this->assertSame([2, 4, 6], $seq2->toArray());
        $this->assertTrue($isGenerated);
    }

    public function testShouldMapSeqAndUseIndexInMapping(): void
    {
        $seq = Seq::from([1, 2, 3, 4, 5]);

        $seq = $seq->mapi(fn ($v, $i) => $i * $v);

        $this->assertSame([0, 2, 6, 12, 20], $seq->toArray());
    }

    public function testShouldMapGeneratedSeqAndUseIndexInMapping(): void
    {
        $seq = Seq::init(function () {
            for ($i = 1; $i < 6; $i++) {
                yield $i;
            }
        });

        $seq = $seq->mapi(fn ($v, $i) => $i * $v);

        $this->assertSame([0, 2, 6, 12, 20], $seq->toArray());
    }

    public function testShouldChangeInfiniteSequenceExampleFromReadme(): void
    {
        $result = Seq::infinite()               // 1, 2, ...
            ->filter(fn ($i) => $i % 2 === 0)   // 2, 4, ...
            ->skip(2)                     // 6, 8, ...
            ->map(fn ($i) => $i * $i)           // 36, 64, ...
            ->takeWhile(fn ($i) => $i < 100)    // 36, 64
            ->reverse()                         // 64, 36
            ->take(1);                     // 64

        $this->assertSame([64], $result->toArray());
    }
}
