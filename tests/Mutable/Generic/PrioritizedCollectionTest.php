<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Fixtures\SimpleEntity;

class PrioritizedCollectionTest extends AbstractTestCase
{
    /** @dataProvider provideItemsByPriority */
    public function testShouldAddItemsAndIterateThemByPriority(array $items, array $expectedItems): void
    {
        $prioritizedCollection = new PrioritizedCollection('any');

        foreach ($items as [$item, $priority]) {
            $prioritizedCollection->add($item, $priority);
        }

        $result = iterator_to_array($prioritizedCollection);

        $this->assertEquals($expectedItems, $result);
    }

    public function provideItemsByPriority(): array
    {
        return [
            // items, expected items
            'empty' => [[], []],
            'strings' => [
                [
                    ['high', 5],
                    ['low', 0],
                    ['medium', 3],
                ],
                ['high', 'medium', 'low'],
            ],
            'string in order' => [
                [
                    ['high', 5],
                    ['medium', 3],
                    ['low', 0],
                ],
                ['high', 'medium', 'low'],
            ],
            'string in reversed order' => [
                [
                    ['low', 0],
                    ['medium', 3],
                    ['high', 5],
                ],
                ['high', 'medium', 'low'],
            ],
            'string with same priority' => [
                [
                    ['medium', 3],
                    ['low', 0],
                    ['high', 5],
                    ['medium 2', 3],
                ],
                ['high', 'medium', 'medium 2', 'low'],
            ],
            'objects' => [
                [
                    [new SimpleEntity(10), 10],
                    [new SimpleEntity(1), 1],
                    [new SimpleEntity(5), 5],
                ],
                [new SimpleEntity(10), new SimpleEntity(5), new SimpleEntity(1)],
            ],
        ];
    }

    public function testShouldCountItems(): void
    {
        $prioritizedCollection = new PrioritizedCollection('int');

        $prioritizedCollection->add(1, 1);
        $this->assertCount(1, $prioritizedCollection);

        $prioritizedCollection->add(5, 5);
        $this->assertCount(2, $prioritizedCollection);
    }

    /** @dataProvider provideInvalidItems */
    public function testShouldNotAddValuesOfDifferentType(string $type, mixed $item, string $expectedMessage): void
    {
        $prioritizedCollection = new PrioritizedCollection($type);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $prioritizedCollection->add($item, 10);
    }

    public function provideInvalidItems(): array
    {
        return [
            // type, item, expectedMessage
            'string -> int[]' => [
                'int',
                'foo',
                'Invalid value type argument "foo"<string> given - <int> expected',
            ],
            'int -> string[]' => [
                'string',
                10,
                'Invalid value type argument "10"<integer> given - <string> expected',
            ],
            'bool -> SimpleEntity[]' => [
                SimpleEntity::class,
                true,
                'Invalid value type argument "true"<boolean> given - <instance of (MF\Collection\Fixtures\SimpleEntity)> expected',
            ],
        ];
    }

    public function testShouldIterateFunctionsByPriority(): void
    {
        $add = function ($a) {
            return function ($b) use ($a) {
                return $a + $b;
            };
        };

        $times = function ($a) {
            return function ($b) use ($a) {
                return $a * $b;
            };
        };

        $functionsByPriority = new PrioritizedCollection('callable');
        $functionsByPriority->add($add(1), 2);
        $functionsByPriority->add($times(2), 1);
        $functionsByPriority->add($times(3), 5);
        $functionsByPriority->add($add(10), 10);
        $functionsByPriority->add($times(0.5), 3);

        $this->assertCount(5, $functionsByPriority);

        $n = 42;
        $expected = ((((42 + 10) * 3) * 0.5) + 1) * 2;

        foreach ($functionsByPriority as $function) {
            $n = $function($n);
        }

        $this->assertSame($expected, $n);
    }
}
