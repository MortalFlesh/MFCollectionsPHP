<?php declare(strict_types=1);

namespace MF\Collection;

use MF\Collection\Exception\CollectionExceptionInterface;

class RangeTest extends AbstractTestCase
{
    /** @dataProvider provideValidRange */
    public function testShouldParseRange(string|array $input, array $expected): void
    {
        $result = Range::parse($input);

        $this->assertSame($expected, $result);
    }

    public function provideValidRange(): array
    {
        return [
            // range, expected
            // ints
            'by string' => ['1..10', [1, 10, 1]],
            'by string with step' => ['1..2..10', [1, 10, 2]],
            'by string with step - infinite' => ['1..2..Inf', [1, Range::INFINITE, 2]],
            'by array' => [[1, 10], [1, 10, 1]],
            'by array with step' => [[1, 2, 10], [1, 10, 2]],
            'by array with step - infinite' => [[1, 2, 'Inf'], [1, Range::INFINITE, 2]],
            // floats
            'float - by array' => [[1.2, 10], [1.2, 10, 1]],
            'float - by array with step' => [[1, 2.5, 10.5], [1, 10.5, 2.5]],
            'float - by array with step - infinite' => [[1.1, 2.2, 'Inf'], [1.1, Range::INFINITE, 2.2]],
        ];
    }

    /** @dataProvider provideInvalidRange */
    public function testShouldNotParseInvalidRange(string|array $invalidInput, string $expectedMessage): void
    {
        $this->expectException(CollectionExceptionInterface::class);
        $this->expectExceptionMessage($expectedMessage);

        Range::parse($invalidInput);
    }

    public function provideInvalidRange(): array
    {
        return [
            // invalidRange, expectedMessage
            'empty array' => [[], 'Range must have [start, end] or [start, step, end] items. 0 values given.'],
            'array with only 1 item' => [
                [42],
                'Range must have [start, end] or [start, step, end] items. 1 values given.',
            ],
            'array with more than 3 items' => [
                [1, 2, 3, 4],
                'Range must have [start, end] or [start, step, end] items. 4 values given.',
            ],
        ];
    }
}
