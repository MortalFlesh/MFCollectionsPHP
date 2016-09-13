<?php

namespace MF\Tests\Collection\Immutable;

use MF\Collection\CollectionInterface;
use MF\Collection\Immutable\ListCollection;
use MF\Collection\Immutable\ListInterface;

/**
 * @group unit
 */
class ListTest extends \PHPUnit_Framework_TestCase
{
    /** @var ListCollection */
    protected $list;

    public function setUp()
    {
        $this->list = new ListCollection();
    }

    public function testShouldImplementsInterfaces()
    {
        $this->assertInstanceOf(ListInterface::class, $this->list);
        $this->assertInstanceOf(CollectionInterface::class, $this->list);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->list);
        $this->assertInstanceOf(\Countable::class, $this->list);
    }

    /**
     * @param array $array
     * @param bool $recursive
     *
     * @dataProvider arrayProvider
     */
    public function testShouldCreateListFromArray(array $array, $recursive)
    {
        $list = ListCollection::createFromArray($array, $recursive);

        $this->assertEquals($array, $list->toArray());
    }

    public function arrayProvider()
    {
        return [
            [
                'array' => [],
                'recursive' => false,
            ],
            [
                'array' => [1, 2, 3],
                'recursive' => false,
            ],
            [
                'array' => [1, 'value', 3],
                'recursive' => true,
            ],
            [
                'array' => [1, 'value', 3, ['val', 4], [[5, 6]]],
                'recursive' => true,
            ],
            [
                'array' => [1, 'value', 3, ['val', 4], [[5, 6]]],
                'recursive' => false,
            ],
        ];
    }

    /**
     * @param bool $recursive
     *
     * @dataProvider recursiveProvider
     */
    public function testShouldCreateListFromArrayWithSubArray($recursive)
    {
        $subArray = ['value'];

        $array = [
            1,
            $subArray,
        ];

        $list = ListCollection::createFromArray($array, $recursive);

        if ($recursive) {
            $this->assertInstanceOf(ListCollection::class, $list->last());
        } else {
            $this->assertEquals($subArray, $list->last());
        }
    }

    public function recursiveProvider()
    {
        return [
            ['recursive' => false],
            ['recursive' => true],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMap($value)
    {
        $newList = $this->list->add($value);

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(1, $newList->count());
    }

    public function addItemsProvider()
    {
        return [
            ['value' => 'string-value'],
            ['value' => 2],
            ['value' => 42],
            ['value' => false],
            ['value' => 24.12],
        ];
    }

    public function testShouldIterateThroughList()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $i = 0;
        foreach ($list as $value) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals(3, $value);
            }
            $i++;
        }
    }

    /**
     * @param array $array
     *
     * @dataProvider arrayProvider
     */
    public function testShouldGetCount(array $array)
    {
        $originalCount = count($array);
        $list = ListCollection::createFromArray($array);

        $this->assertCount($originalCount, $list);

        $newList = $list->add('value');
        $this->assertCount($originalCount, $list);
        $this->assertCount($originalCount + 1, $newList);
    }

    public function testShouldHasValue()
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list = $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->contains($valueExists));
        $this->assertFalse($this->list->contains($valueDoesNotExist));
    }

    public function testShouldRemoveFirst()
    {
        $value = 'value';

        $this->list = $this->list->add($value);
        $this->list = $this->list->add($value);

        $this->assertCount(2, $this->list);
        $this->assertEquals(2, $this->list->count());
        $this->assertTrue($this->list->contains($value));

        $listWithoutValue = $this->list->removeFirst($value);

        $this->assertCount(2, $this->list);
        $this->assertCount(1, $listWithoutValue);

        $this->assertEquals(2, $this->list->count());
        $this->assertEquals(1, $listWithoutValue->count());

        $this->assertTrue($this->list->contains($value));
        $this->assertTrue($listWithoutValue->contains($value));

        $this->assertEquals($value, $this->list->first());
        $this->assertEquals($value, $listWithoutValue->first());
    }

    public function testShouldNotRemoveFirstValue()
    {
        $this->list = $this->list->add('value');

        $this->assertCount(1, $this->list);
        $this->assertTrue($this->list->contains('value'));
        $this->assertFalse($this->list->contains('not-existed-value'));

        $this->list = $this->list->removeFirst('not-existed-value');

        $this->assertCount(1, $this->list);
        $this->assertTrue($this->list->contains('value'));
        $this->assertFalse($this->list->contains('not-existed-value'));
    }

    public function testShouldRemoveAll()
    {
        $value = 'value';
        $value2 = 'value2';

        $this->list = $this->list->add($value);
        $this->list = $this->list->add($value2);
        $this->list = $this->list->add($value);

        $this->assertCount(3, $this->list);
        $this->assertEquals(3, $this->list->count());
        $this->assertTrue($this->list->contains($value));
        $this->assertTrue($this->list->contains($value2));

        $listWithoutValue = $this->list->removeAll($value);

        $this->assertCount(3, $this->list);
        $this->assertCount(1, $listWithoutValue);

        $this->assertTrue($this->list->contains($value));
        $this->assertFalse($listWithoutValue->contains($value));

        $this->assertTrue($this->list->contains($value2));
        $this->assertTrue($listWithoutValue->contains($value2));
    }

    public function testShouldAddValueToEndOfList()
    {
        $value = 'value';
        $value2 = 'value2';

        $this->list = $this->list->add($value);
        $this->assertEquals($value, $this->list->last());

        $this->list = $this->list->add($value2);
        $this->assertEquals($value2, $this->list->last());
    }

    public function testShouldUnshiftValue()
    {
        $value = 'value';
        $value2 = 'value2';
        $valueToUnshift = 'valueToUnshift';

        $this->list = $this->list->add($value);
        $this->assertEquals($value, $this->list->first());

        $this->list = $this->list->add($value2);
        $this->assertEquals($value, $this->list->first());

        $newList = $this->list->unshift($valueToUnshift);
        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals($valueToUnshift, $newList->first());
    }

    public function testShouldSortValues()
    {
        $list = ListCollection::createFromArray([1, 4, 3, 4, 2, 5, 4]);

        $sortedList = $list->sort();

        $this->assertNotEquals($list, $sortedList);
        $this->assertEquals([1, 2, 3, 4, 4, 4, 5], $sortedList->toArray());
    }

    public function testShouldForeachItemInList()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $list->each(function ($value, $i) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 1) {
                $this->assertEquals(3, $value);
            }
        });
    }

    public function testShouldMapItemsToNewList()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $newList = $list->map(function ($value, $i) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals(3, $value);
            }

            return $i . $value;
        });

        $this->assertNotEquals($list, $newList);
        $this->assertEquals([0 => '0one', 1 => '1two', 2 => '23'], $newList->toArray());
    }

    public function testShouldFilterMapToNewList()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $newList = $list->filter(function ($value, $i) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals(3, $value);
            }

            return is_string($value);
        });

        $this->assertEquals([0 => 'one', 1 => 'two'], $newList->toArray());
    }

    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToEach()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->each(1);
    }

    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToMap()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->map(1);
    }

    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToFilter()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->filter(1);
    }

    public function testShouldCallReducerCorrectly()
    {
        $this->list = $this->list->add('value');

        $reduced = $this->list->reduce(function ($total, $current, $key, $list) {
            $this->assertEquals('initial', $total);
            $this->assertEquals('value', $current);
            $this->assertEquals(0, $key);
            $this->assertSame($this->list, $list);

            return $total . $current . $key;
        }, 'initial');

        $this->assertEquals('initialvalue0', $reduced);
    }

    /**
     * @param callable $reducer
     * @param array $values
     * @param mixed $expected
     *
     * @dataProvider reduceProvider
     */
    public function testShouldReduceList(callable $reducer, array $values, $expected)
    {
        foreach ($values as $value) {
            $this->list = $this->list->add($value);
        }

        $this->assertEquals($expected, $this->list->reduce($reducer));
    }

    public function reduceProvider()
    {
        return [
            'total count' => [
                function ($total, $current) {
                    return $total + $current;
                },
                [1, 2, 3, 4, 5],
                15,
            ],
            'concat strings with indexes' => [
                function ($total, $current, $index, ListCollection $list) {
                    $next = sprintf('%s_%d', $current, $index);
                    $delimiter = count($list) - 1 === $index ? '' : '|';

                    return $total . $next . $delimiter;
                },
                ['one', 'two', 'three'],
                'one_0|two_1|three_2',
            ],
        ];
    }

    /**
     * @param callable $reducer
     * @param array $values
     * @param mixed $initialValue
     * @param mixed $expected
     *
     * @dataProvider reduceInitialProvider
     */
    public function testShouldReduceListWithInitialValue(callable $reducer, array $values, $initialValue, $expected)
    {
        foreach ($values as $value) {
            $this->list = $this->list->add($value);
        }

        $this->assertEquals($expected, $this->list->reduce($reducer, $initialValue));
    }

    public function reduceInitialProvider()
    {
        return [
            'total count' => [
                function ($total, $current) {
                    return $total + $current;
                },
                [1, 2, 3, 4, 5],
                10,
                25,
            ],
            'total count with empty list' => [
                function ($total, $current) {
                    return $total + $current;
                },
                [],
                10,
                10,
            ],
            'concat strings with indexes' => [
                function ($total, $current, $index, ListCollection $list) {
                    $next = sprintf('%s_%d', $current, $index);
                    $delimiter = $list->count() - 1 === $index ? '' : '|';

                    return $total . $next . $delimiter;
                },
                ['one', 'two', 'three'],
                'initial-',
                'initial-one_0|two_1|three_2',
            ],
        ];
    }

    public function testShouldGetImmutableListAsMutable()
    {
        $this->list = $this->list->add('value');

        $mutable = $this->list->asMutable();

        $this->assertInstanceOf(\MF\Collection\ListInterface::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\ListCollection::class, $mutable);

        $this->assertEquals($this->list->toArray(), $mutable->toArray());
    }

    public function testShouldClearCollection()
    {
        $this->list = $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));

        $this->list = $this->list->clear();
        $this->assertFalse($this->list->contains('value'));
    }

    public function testShouldCheckIfCollectionIsEmpty()
    {
        $this->list = $this->list->add('value');
        $this->assertFalse($this->list->isEmpty());

        $this->list = $this->list->clear();
        $this->assertTrue($this->list->isEmpty());
    }
}
