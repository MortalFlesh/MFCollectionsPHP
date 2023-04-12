<?php declare(strict_types=1);

namespace MF\Collection\Helper;

use MF\Collection\AbstractTestCase;
use MF\Collection\Immutable\Generic\ICollection as ImmutableICollection;
use MF\Collection\Immutable\Generic\ListCollection as ImmutableListCollection;
use MF\Collection\Immutable\Generic\Map as ImmutableMap;
use MF\Collection\Immutable\Generic\Seq;
use MF\Collection\Mutable\Generic\ICollection;
use MF\Collection\Mutable\Generic\ListCollection;
use MF\Collection\Mutable\Generic\Map;

class CollectionTest extends AbstractTestCase
{
    /** @dataProvider provideMutableCollection */
    public function testShouldTransformMutableCollectionToArray(ICollection $collection, array $expected): void
    {
        $result = Collection::mutableToArray($collection);

        $this->assertSame($expected, $result);
    }

    public static function provideMutableCollection(): array
    {
        return [
            // collection, expected
            'empty list' => [new ListCollection(), []],
            'int list' => [ListCollection::of(1, 2, 3), [1, 2, 3]],
            'array list' => [ListCollection::from([[], []]), [[], []]],
            'list in list' => [
                ListCollection::from([ListCollection::of(1, 2, 3), ListCollection::from([4, 5])]),
                [[1, 2, 3], [4, 5]],
            ],
            'nested in list' => [
                ListCollection::from([
                    ImmutableListCollection::of(
                        ImmutableListCollection::of(1, 2, 3),
                        Seq::from([4, 5]),
                    ),
                    ListCollection::from([6, 7]),
                ]),
                [[[1, 2, 3], [4, 5]], [6, 7]],
            ],

            'empty map' => [new Map(), []],
            'string/int map' => [Map::from(['one' => 1, 'two' => 2]), ['one' => 1, 'two' => 2]],
            'nested in map' => [
                Map::from([
                    'nested list' => ImmutableListCollection::of(
                        ImmutableListCollection::of(1, 2, 3),
                        Seq::from([4, 5]),
                    ),
                    'list' => ListCollection::from([6, 7]),
                ]),
                [
                    'nested list' => [[1, 2, 3], [4, 5]],
                    'list' => [6, 7],
                ],
            ],
        ];
    }

    /** @dataProvider provideImmutableCollection */
    public function testShouldTransformImmutableCollectionToArray(
        ImmutableICollection $collection,
        array $expected,
    ): void {
        $result = Collection::immutableToArray($collection);

        $this->assertSame($expected, $result);
    }

    public static function provideImmutableCollection(): array
    {
        return [
            // collection, expected
            'empty list' => [new ImmutableListCollection(), []],
            'int list' => [ImmutableListCollection::of(1, 2, 3), [1, 2, 3]],
            'array list' => [ImmutableListCollection::from([[], []]), [[], []]],
            'list in list' => [
                ImmutableListCollection::from([ListCollection::of(1, 2, 3), ListCollection::from([4, 5])]),
                [[1, 2, 3], [4, 5]],
            ],
            'nested in list' => [
                ImmutableListCollection::from([
                    ImmutableListCollection::of(
                        ImmutableListCollection::of(1, 2, 3),
                        Seq::from([4, 5]),
                    ),
                    ListCollection::from([6, 7]),
                ]),
                [[[1, 2, 3], [4, 5]], [6, 7]],
            ],

            'empty map' => [new ImmutableMap(), []],
            'string/int map' => [ImmutableMap::from(['one' => 1, 'two' => 2]), ['one' => 1, 'two' => 2]],
            'nested in map' => [
                ImmutableMap::from([
                    'nested list' => ImmutableListCollection::of(
                        ImmutableListCollection::of(1, 2, 3),
                        Seq::from([4, 5]),
                    ),
                    'list' => ListCollection::from([6, 7]),
                ]),
                [
                    'nested list' => [[1, 2, 3], [4, 5]],
                    'list' => [6, 7],
                ],
            ],
        ];
    }
}
