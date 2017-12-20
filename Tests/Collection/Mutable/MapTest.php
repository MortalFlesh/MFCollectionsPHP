<?php

namespace MF\Tests\Collection\Mutable;

use MF\Collection\IMap as BaseMapInterface;
use MF\Collection\Mutable\ICollection;
use MF\Collection\Mutable\IList;
use MF\Collection\Mutable\IMap;
use MF\Collection\Mutable\Map;
use MF\Tests\AbstractTestCase;

class MapTest extends AbstractTestCase
{
    /** @var IMap */
    protected $map;

    public function setUp()
    {
        $this->map = new Map();
    }

    public function testShouldImplementsInterfaces()
    {
        $this->assertInstanceOf(BaseMapInterface::class, $this->map);
        $this->assertInstanceOf(IMap::class, $this->map);
        $this->assertInstanceOf(ICollection::class, $this->map);
        $this->assertInstanceOf(\ArrayAccess::class, $this->map);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->map);
        $this->assertInstanceOf(\Countable::class, $this->map);
    }

    /**
     * @param array $array
     * @param bool $recursive
     *
     * @dataProvider arrayProvider
     */
    public function testShouldCreateMapFromArray(array $array, $recursive)
    {
        $map = Map::from($array, $recursive);

        $this->assertEquals($array, $map->toArray());
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
                'array' => [1, 'value', 3, ['val', 4], ['array' => [5, 6]]],
                'recursive' => true,
            ],
            [
                'array' => [1, 'value', 3, ['val', 4], ['array' => [5, 6]]],
                'recursive' => false,
            ],
        ];
    }

    /**
     * @param bool $recursive
     *
     * @dataProvider recursiveProvider
     */
    public function testShouldCreateMapFromArrayWithSubArray($recursive)
    {
        $key = 'array-key';
        $subArray = ['key' => 'value'];

        $array = [
            'key' => 1,
            $key => $subArray,
        ];

        $map = Map::from($array, $recursive);

        if ($recursive) {
            $this->assertInstanceOf(Map::class, $map[$key]);
        } else {
            $this->assertEquals($subArray, $map[$key]);
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
     * @param mixed $key
     * @param mixed $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMapArrayWay($key, $value)
    {
        $this->map[$key] = $value;

        $this->assertEquals($value, $this->map[$key]);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMap($key, $value)
    {
        $this->map->set($key, $value);

        $this->assertEquals($value, $this->map->get($key));
    }

    public function addItemsProvider()
    {
        return [
            [
                'key' => 'string-key',
                'value' => 'string-value',
            ],
            [
                'key' => 1,
                'value' => 2,
            ],
            [
                'key' => '5',
                'value' => 42,
            ],
            [
                'key' => true,
                'value' => false,
            ],
            [
                'key' => 24.23,
                'value' => 24.12,
            ],
        ];
    }

    /**
     * @param object|array $key
     *
     * @dataProvider invalidKeyProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnAddingObjectArrayWay($key)
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->map->set($key, 'value');
    }

    /**
     * @param object|array $key
     *
     * @dataProvider invalidKeyProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnAddingObject($key)
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->map[$key] = 'value';
    }

    public function invalidKeyProvider()
    {
        return [
            ['key' => new \stdClass()],
            ['key' => []],
        ];
    }

    public function testShouldIterateThroughMap()
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $i = 0;
        foreach ($map as $key => $value) {
            if ($i === 0) {
                $this->assertEquals(1, $key);
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals(2, $key);
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals('three', $key);
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
        $map = Map::from($array);

        $this->assertCount($originalCount, $map);

        $map->set('key', 'value');
        $this->assertCount($originalCount + 1, $map);

        $map['key'] = 'value X';
        $this->assertCount($originalCount + 1, $map);

        $map['keyY'] = 'value Y';
        $this->assertCount($originalCount + 2, $map);
    }

    public function testShouldHasKeys()
    {
        $keyExists = 'has-key';
        $keyDoesNotExist = 'has-no-key';

        $this->map->set($keyExists, 'value');

        $this->assertArrayHasKey($keyExists, $this->map);
        $this->assertArrayNotHasKey($keyDoesNotExist, $this->map);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesNotExist));
    }

    public function testShouldRemoveItem()
    {
        $key = 'key';
        $key2 = 'key2';

        $this->map->set($key, 'value');
        $this->assertTrue($this->map->containsKey($key));

        $this->map[$key2] = 'value2';
        $this->assertTrue($this->map->containsKey($key2));

        $this->map->remove($key);
        $this->assertFalse($this->map->containsKey($key));

        unset($this->map[$key2]);
        $this->assertFalse($this->map->containsKey($key2));
    }

    public function testShouldContainsValue()
    {
        $key = 'key';
        $value = 1;
        $valueNotPresented = 4;

        $this->map->set($key, $value);

        $this->assertTrue($this->map->contains($value));
        $this->assertFalse($this->map->contains($valueNotPresented));
    }

    public function testShouldForeachItemInMap()
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $map->each(function ($value, $key) {
            if ($key === 1) {
                $this->assertEquals('one', $value);
            } elseif ($key === 2) {
                $this->assertEquals('two', $value);
            } elseif ($key === 'three') {
                $this->assertEquals(3, $value);
            }
        });
    }

    public function testShouldMapItemsToNewMap()
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $newMap = $map->map(function ($key, $value) {
            if ($key === 1) {
                $this->assertEquals('one', $value);
            } elseif ($key === 2) {
                $this->assertEquals('two', $value);
            } elseif ($key === 'three') {
                $this->assertEquals(3, $value);
            }

            return $key . $value;
        });

        $this->assertNotEquals($map, $newMap);
        $this->assertEquals([1 => '1one', 2 => '2two', 'three' => 'three3'], $newMap->toArray());
    }

    public function testShouldFilterMapToNewMap()
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $newMap = $map->filter(function ($key, $value) {
            if ($key === 1) {
                $this->assertEquals('one', $value);
            } elseif ($key === 2) {
                $this->assertEquals('two', $value);
            } elseif ($key === 'three') {
                $this->assertEquals(3, $value);
            }

            return is_string($key);
        });

        $this->assertEquals(['three' => 3], $newMap->toArray());
    }

    public function testShouldGetKeys()
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $keys = $map->keys();

        $this->assertInstanceOf(IList::class, $keys);
        $this->assertEquals([1, 2, 'three'], $keys->toArray());
    }

    public function testShouldGetValueArrayWay()
    {
        $this->map->set('key', 'value');

        $this->assertEquals('value', $this->map['key']);
        $this->assertEquals('value', $this->map->get('key'));
    }

    public function testShouldGetValues()
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $values = $map->values();

        $this->assertInstanceOf(IList::class, $values);
        $this->assertEquals(['one', 'two', 3], $values->toArray());
    }

    public function testShouldCallReducerCorrectly()
    {
        $this->map->set('key', 'value');

        $reduced = $this->map->reduce(function ($total, $current, $key, $map) {
            $this->assertEquals('initial', $total);
            $this->assertEquals('value', $current);
            $this->assertEquals('key', $key);
            $this->assertSame($this->map, $map);

            return $total . $current . $key;
        }, 'initial');

        $this->assertEquals('initialvaluekey', $reduced);
    }

    /**
     * @param callable $reducer
     * @param array $values
     * @param mixed $expected
     *
     * @dataProvider reduceProvider
     */
    public function testShouldReduceMap(callable $reducer, array $values, $expected)
    {
        foreach ($values as $key => $value) {
            $this->map->set($key, $value);
        }

        $this->assertEquals($expected, $this->map->reduce($reducer));
    }

    public function reduceProvider()
    {
        return [
            'total count' => [
                function ($total, $current) {
                    return $total + $current;
                },
                ['one' => 1, 'two' => 2, 'three' => 3],
                6,
            ],
            'concat strings with indexes' => [
                function ($total, $current, $index, Map $map) {
                    $next = sprintf('%s_%d', $current, $index);

                    return $total . $next . '|';
                },
                [1 => 'one', 2 => 'two', 3 => 'three'],
                'one_1|two_2|three_3|',
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
    public function testShouldReduceMapWithInitialValue(callable $reducer, array $values, $initialValue, $expected)
    {
        foreach ($values as $key => $value) {
            $this->map->set($key, $value);
        }

        $this->assertEquals($expected, $this->map->reduce($reducer, $initialValue));
    }

    public function reduceInitialProvider()
    {
        return [
            'total count' => [
                function ($total, $current) {
                    return $total + $current;
                },
                ['one' => 1, 'two' => 2, 'three' => 3],
                10,
                16,
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
                function ($total, $current, $index, Map $map) {
                    $next = sprintf('%s_%d', $current, $index);

                    return $total . $next . '|';
                },
                [1 => 'one', 2 => 'two', 3 => 'three'],
                'initial-',
                'initial-one_1|two_2|three_3|',
            ],
        ];
    }

    public function testShouldGetMutableListAsImmutable()
    {
        $this->map->set('key', 'value');

        $immutable = $this->map->asImmutable();

        $this->assertInstanceOf(\MF\Collection\Immutable\IMap::class, $immutable);
        $this->assertInstanceOf(\MF\Collection\Immutable\Map::class, $immutable);

        $this->assertEquals($this->map->toArray(), $immutable->toArray());
    }

    public function testShouldClearCollection()
    {
        $this->map->set('key', 'value');
        $this->assertTrue($this->map->contains('value'));

        $this->map->clear();
        $this->assertFalse($this->map->contains('value'));
    }

    public function testShouldCheckIfCollectionIsEmpty()
    {
        $this->map->set('key', 'value');
        $this->assertFalse($this->map->isEmpty());

        $this->map->clear();
        $this->assertTrue($this->map->isEmpty());
    }

    public function testShouldMapBigCollectionManyTimesInOneLoop()
    {
        $this->startTimer();
        $bigMap = Map::from(range(0, 10000));
        $creatingCollection = $this->stopTimer();

        $this->startTimer();
        foreach ($bigMap as $i) {
            // loop over all items
        }
        $loopTime = $this->stopTimer();

        $this->startTimer();
        $bigMap
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
        foreach ($bigMap as $item) {
            // loop over all items
        }
        $loopWithMappingTime = $this->stopTimer();

        $totalTime = $creatingCollection + $loopTime + $mappingTime + $loopWithMappingTime;

        $this->assertLessThan(1, $mappingTime);
        $this->assertLessThan($loopTime * 1.5, $loopWithMappingTime);   // 50% is still fair enough
        $this->assertCount(10001, $bigMap);

        // this test before lazy mapping lasts around 5-6 seconds, and now it is less than 2 seconds
        $this->assertLessThan(2000, $totalTime);
    }
}
