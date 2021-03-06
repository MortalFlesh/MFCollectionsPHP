<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use MF\Collection\AbstractTestCase;
use MF\Collection\ICollection;

class ListTest extends AbstractTestCase
{
    /** @var ListCollection|IList */
    protected $list;

    protected function setUp(): void
    {
        $this->list = new ListCollection();
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(IList::class, $this->list);
        $this->assertInstanceOf(ICollection::class, $this->list);
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

    /** @dataProvider arrayProvider */
    public function testShouldCreateListFromArray(array $array, bool $recursive): void
    {
        $list = ListCollection::from($array, $recursive);

        $this->assertEquals($array, $list->toArray());
    }

    public function arrayProvider(): array
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

    /** @dataProvider recursiveProvider */
    public function testShouldCreateListFromArrayWithSubArray(bool $recursive): void
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

    public function recursiveProvider(): array
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

    /** @dataProvider addItemsProvider */
    public function testShouldAddItemsToMap(mixed $value): void
    {
        $newList = $this->list->add($value);

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(1, $newList->count());
    }

    public function addItemsProvider(): array
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

        $newList = $list->add('value');
        $this->assertCount($originalCount, $list);
        $this->assertCount($originalCount + 1, $newList);
    }

    public function testShouldHasValue(): void
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list = $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->contains($valueExists));
        $this->assertFalse($this->list->contains($valueDoesNotExist));
    }

    public function testShouldHasValueBy(): void
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list = $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->containsBy($this->findByValue($valueExists)));
        $this->assertFalse($this->list->containsBy($this->findByValue($valueDoesNotExist)));
    }

    public function testShouldRemoveFirst(): void
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

    public function testShouldNotRemoveFirstValue(): void
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

    public function testShouldRemoveAll(): void
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

    public function testShouldAddValueToEndOfList(): void
    {
        $value = 'value';
        $value2 = 'value2';

        $this->list = $this->list->add($value);
        $this->assertEquals($value, $this->list->last());

        $this->list = $this->list->add($value2);
        $this->assertEquals($value2, $this->list->last());
    }

    public function testShouldUnshiftValue(): void
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

    public function testShouldGetFirstValue(): void
    {
        $this->assertNull($this->list->first());

        $this->list = $this->list->add('first');
        $this->list = $this->list->add('second');

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

        $this->list = $this->list->add('first');
        $this->list = $this->list->add('second');

        $this->assertSame('second', $this->list->firstBy($findSecond));
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
            } elseif ($i === 2) {
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

    /** @dataProvider reduceProvider */
    public function testShouldReduceList(callable $reducer, array $values, mixed $expected): void
    {
        foreach ($values as $value) {
            $this->list = $this->list->add($value);
        }

        $this->assertEquals($expected, $this->list->reduce($reducer));
    }

    public function reduceProvider(): array
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

    /** @dataProvider reduceInitialProvider */
    public function testShouldReduceListWithInitialValue(
        callable $reducer,
        array $values,
        mixed $initialValue,
        mixed $expected
    ): void {
        foreach ($values as $value) {
            $this->list = $this->list->add($value);
        }

        $this->assertEquals($expected, $this->list->reduce($reducer, $initialValue));
    }

    public function reduceInitialProvider(): array
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

    public function testShouldGetImmutableListAsMutable(): void
    {
        $this->list = $this->list->add('value');

        $mutable = $this->list->asMutable();

        $this->assertInstanceOf(\MF\Collection\IList::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\ListCollection::class, $mutable);

        $this->assertEquals($this->list->toArray(), $mutable->toArray());
    }

    public function testShouldClearCollection(): void
    {
        $this->list = $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));

        $this->list = $this->list->clear();
        $this->assertFalse($this->list->contains('value'));
    }

    public function testShouldCheckIfCollectionIsEmpty(): void
    {
        $this->list = $this->list->add('value');
        $this->assertFalse($this->list->isEmpty());

        $this->list = $this->list->clear();
        $this->assertTrue($this->list->isEmpty());
    }

    public function testShouldMapAndFilterImmutableCollection(): void
    {
        $list = ListCollection::from([1, 2, 3]);
        $add1 = function ($i) {
            return $i + 1;
        };
        $double = function ($i) {
            return $i * 2;
        };
        $even = function ($i) {
            return $i % 2 === 0;
        };

        $listAdd1 = $list->map($add1)->add(10);
        $this->assertSame([2, 3, 4, 10], $listAdd1->toArray());

        $listDouble = $list->map($double);
        $this->assertSame([2, 4, 6], $listDouble->toArray());

        $listAdd1EvenAndDouble = $listAdd1
            ->map($double)
            ->filter($even);
        $this->assertSame([4, 6, 8, 20], $listAdd1EvenAndDouble->toArray());

        $this->assertSame([1, 2, 3], $list->toArray());
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

        $this->assertLessThan(1.5, $mappingTime);
        $this->assertLessThan($this->forPHP(['80' => $loopTime * 1.4]), $loopWithMappingTime);
        $this->assertCount(10001, $bigList);

        // this test lasts much longer before lazy mapping, now it is faster
        $this->assertLessThan($this->forPHP(['80' => 500]), $totalTime);
    }

    public function testShouldImplodeItems(): void
    {
        $list = ListCollection::of(1, 2, 3);

        $result = $list->implode(', ');

        $this->assertSame('1, 2, 3', $result);
    }
}
