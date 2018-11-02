<?php declare(strict_types=1);

namespace MF\Collection\Mutable;

use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\CollectionExceptionInterface;

class ListTest extends AbstractTestCase
{
    /** @var ListCollection */
    protected $list;

    protected function setUp(): void
    {
        $this->list = new ListCollection();
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(ICollection::class, $this->list);
        $this->assertInstanceOf(IList::class, $this->list);
        $this->assertInstanceOf(\MF\Collection\ICollection::class, $this->list);
        $this->assertInstanceOf(\MF\Collection\IList::class, $this->list);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->list);
        $this->assertInstanceOf(\Countable::class, $this->list);
    }

    public function testShouldCreateListOfValues(): void
    {
        $list = ListCollection::of(1, 2, 3);
        $this->assertEquals([1, 2, 3], $list->toArray());

        $values = [1, 2, 3];
        $values2 = [4, 5, 6];
        $list = ListCollection::of(...$values, ...$values2);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $list->toArray());
    }

    /**
     * @param bool $recursive
     *
     * @dataProvider arrayProvider
     */
    public function testShouldCreateListFromArray(array $array, $recursive): void
    {
        $list = ListCollection::from($array, $recursive);

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
    public function testShouldCreateListFromArrayWithSubArray($recursive): void
    {
        $subArray = ['value'];

        $array = [
            1,
            $subArray,
        ];

        $list = ListCollection::from($array, $recursive);

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

    public function testShouldCreateListByCallback(): void
    {
        $list = ListCollection::create(
            explode(',', '1, 2, 3'),
            function ($value) {
                return (int) $value;
            }
        );

        $this->assertSame([1, 2, 3], $list->toArray());
    }

    /**
     * @param mixed $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMap($value): void
    {
        $this->list->add($value);

        $this->assertEquals($value, $this->list->pop());
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

    public function testShouldIterateThroughList(): void
    {
        $list = ListCollection::from(['one', 'two', 3]);

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
     * @dataProvider arrayProvider
     */
    public function testShouldGetCount(array $array): void
    {
        $originalCount = count($array);
        $list = ListCollection::from($array);

        $this->assertCount($originalCount, $list);

        $list->add('value');
        $this->assertCount($originalCount + 1, $list);
    }

    public function testShouldHasValue(): void
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->contains($valueExists));
        $this->assertFalse($this->list->contains($valueDoesNotExist));
    }

    public function testShouldHasValueBy(): void
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->containsBy($this->findByValue($valueExists)));
        $this->assertFalse($this->list->containsBy($this->findByValue($valueDoesNotExist)));
    }

    public function testShouldGetFirstValue(): void
    {
        $this->assertNull($this->list->first());

        $this->list->add('first');
        $this->list->add('second');

        $this->assertSame('first', $this->list->first());

        foreach ($this->list as $value) {
            $this->assertSame('first', $this->list->first());
        }
    }

    public function testShouldGetFirstValueBy(): void
    {
        $findSecond = function ($value) {
            return $value === 'second';
        };

        $this->assertNull($this->list->firstBy($findSecond));

        $this->list->add('first');
        $this->list->add('second');

        $this->assertSame('second', $this->list->firstBy($findSecond));
    }

    public function testShouldRemoveFirst(): void
    {
        $value = 'value';

        $this->list->add($value);
        $this->list->add($value);

        $this->assertCount(2, $this->list);
        $this->assertEquals(2, $this->list->count());
        $this->assertTrue($this->list->contains($value));

        $this->list->removeFirst($value);

        $this->assertCount(1, $this->list);
        $this->assertEquals(1, $this->list->count());
        $this->assertTrue($this->list->contains($value));

        $this->assertEquals($value, $this->list->first());
    }

    public function testShouldRemoveAll(): void
    {
        $value = 'value';
        $value2 = 'value2';

        $this->list->add($value);
        $this->list->add($value2);
        $this->list->add($value);

        $this->assertCount(3, $this->list);
        $this->assertEquals(3, $this->list->count());
        $this->assertTrue($this->list->contains($value));
        $this->assertTrue($this->list->contains($value2));

        $this->list->removeAll($value);

        $this->assertCount(1, $this->list);
        $this->assertEquals(1, $this->list->count());
        $this->assertFalse($this->list->contains($value));
        $this->assertTrue($this->list->contains($value2));
    }

    public function testShouldAddValueToEndOfList(): void
    {
        $value = 'value';
        $value2 = 'value2';

        $this->assertNull($this->list->last());

        $this->list->add($value);
        $this->assertEquals($value, $this->list->last());

        $this->list->add($value2);
        $this->assertEquals($value2, $this->list->last());
    }

    public function testShouldUnshiftValue(): void
    {
        $value = 'value';
        $value2 = 'value2';
        $valueToUnshift = 'valueToUnshift';

        $this->list->add($value);
        $this->assertEquals($value, $this->list->first());

        $this->list->add($value2);
        $this->assertEquals($value, $this->list->first());

        $this->list->unshift($valueToUnshift);
        $this->assertEquals($valueToUnshift, $this->list->first());
    }

    public function testShouldShiftValueFromStart(): void
    {
        $firstValue = 'value';
        $value2 = 'value2';

        $this->list->add($firstValue);
        $this->list->add($value2);
        $this->assertCount(2, $this->list);

        $result = $this->list->shift();
        $this->assertCount(1, $this->list);
        $this->assertEquals($firstValue, $result);
    }

    public function testShouldPopValueFromEnd(): void
    {
        $value = 'value';
        $lastValue = 'value2';

        $this->list->add($value);
        $this->list->add($lastValue);
        $this->assertCount(2, $this->list);

        $result = $this->list->pop();
        $this->assertCount(1, $this->list);
        $this->assertEquals($lastValue, $result);
    }

    public function testShouldSortValues(): void
    {
        $list = ListCollection::from([1, 4, 3, 4, 2, 5, 4]);

        $sortedList = $list->sort();

        $this->assertNotEquals($list, $sortedList);
        $this->assertEquals([1, 2, 3, 4, 4, 4, 5], $sortedList->toArray());
    }

    public function testShouldForeachItemInList(): void
    {
        $list = ListCollection::from(['one', 'two', 3]);

        $list->each(function ($value, $i): void {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 1) {
                $this->assertEquals(3, $value);
            }
        });
    }

    public function testShouldMapItemsToNewList(): void
    {
        $list = ListCollection::from(['one', 'two', 3]);

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

    public function testShouldFilterMapToNewList(): void
    {
        $list = ListCollection::from(['one', 'two', 3]);

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

    public function testShouldCallReducerCorrectly(): void
    {
        $this->list->add('value');

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
     * @param mixed $expected
     *
     * @dataProvider reduceProvider
     */
    public function testShouldReduceList(callable $reducer, array $values, $expected): void
    {
        foreach ($values as $value) {
            $this->list->add($value);
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
     * @param mixed $initialValue
     * @param mixed $expected
     *
     * @dataProvider reduceInitialProvider
     */
    public function testShouldReduceListWithInitialValue(
        callable $reducer,
        array $values,
        $initialValue,
        $expected
    ): void {
        foreach ($values as $value) {
            $this->list->add($value);
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

    public function testShouldGetMutableListAsImmutable(): void
    {
        $this->list->add('value');

        $immutable = $this->list->asImmutable();

        $this->assertInstanceOf(\MF\Collection\Immutable\IList::class, $immutable);
        $this->assertInstanceOf(\MF\Collection\Immutable\ListCollection::class, $immutable);

        $this->assertEquals($this->list->toArray(), $immutable->toArray());
    }

    public function testShouldClearCollection(): void
    {
        $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));

        $this->list->clear();
        $this->assertFalse($this->list->contains('value'));
    }

    public function testShouldCheckIfCollectionIsEmpty(): void
    {
        $this->list->add('value');
        $this->assertFalse($this->list->isEmpty());

        $this->list->clear();
        $this->assertTrue($this->list->isEmpty());
    }

    public function testShouldMapAndFilterCollectionToNewListCollectionByArrowFunction(): void
    {
        $this->list = ListCollection::from([1, 2, 3]);

        $newListCollection = $this->list
            ->map(function ($v) {
                return $v + 1;
            })// 2, 3, 4
            ->map(function ($v) {
                return $v * 2;
            })// 4, 6, 8
            ->filter(function ($v) {
                return $v % 3 === 0;
            })// 6
            ->map(function ($v) {
                return $v - 1;
            }); // 5

        $this->assertNotEquals($this->list, $newListCollection);
        $this->assertEquals([5], $newListCollection->toArray());
        $this->assertEquals([5], $newListCollection->toArray());
    }

    public function testShouldMapAndFilterCollectionOnIteration(): void
    {
        $this->list = ListCollection::from([1, 2, 3]);

        $newListCollection = $this->list
            ->map(function ($v) {
                return $v + 1;
            })// 2, 3, 4
            ->map(function ($v) {
                return $v * 2;
            })// 4, 6, 8
            ->filter(function ($v) {
                return $v % 3 === 0;
            })// 6
            ->map(function ($v) {
                return $v - 1;
            }); // 5

        $result = [];
        foreach ($newListCollection as $item) {
            $result[] = $item;
        }

        $this->assertSame([5], $result);
    }

    public function testShouldMapBigCollectionManyTimesInOneLoop(): void
    {
        $this->startTimer();
        $bigList = ListCollection::from(range(0, 10000));
        $creatingCollection = $this->stopTimer();

        $this->startTimer();
        foreach ($bigList as $i) {
            $this->ignore($i);
        }
        $loopTime = $this->stopTimer();

        $this->startTimer();
        $bigList
            ->map(function ($v) {
                return $v + 1;
            })
            ->map(function ($v) {
                return $v * 2;
            })
            ->filter(function ($v) {
                return $v % 2 === 0;
            })
            ->map(function ($v) {
                return $v - 1;
            });
        $mappingTime = $this->stopTimer();

        $this->startTimer();
        foreach ($bigList as $i) {
            $this->ignore($i);
        }
        $loopWithMappingTime = $this->stopTimer();

        $totalTime = $creatingCollection + $loopTime + $mappingTime + $loopWithMappingTime;

        $this->assertLessThan(1, $mappingTime);
        $this->assertLessThan($loopTime * 1.5, $loopWithMappingTime);   // 50% is still fair enough
        $this->assertCount(10001, $bigList);

        // this test before lazy mapping lasts around 5-6 seconds, so now it is more than 3 times faster
        if ($totalTime > $this->forPHP(['71' => 1700, '72' => 2500])) {
            $this->markAsRisky();
        }
    }

    public function testShouldImplodeItems(): void
    {
        $list = ListCollection::of(1, 2, 3);

        $result = $list
            ->map(function ($i) {
                return $i . '_';
            })
            ->implode(', ');

        $this->assertSame('1_, 2_, 3_', $result);
    }

    public function testShouldNotMapByInvalidCallback(): void
    {
        $this->expectException(CollectionExceptionInterface::class);

        $this->list->map('123');
    }
}
