<?php

namespace MF\Tests\Collection\Immutable;

use MF\Collection\CollectionInterface;
use MF\Collection\Immutable\ListInterface;
use MF\Collection\Immutable\Map;
use MF\Collection\Immutable\MapInterface;

/**
 * @group unit
 */
class MapTest extends \PHPUnit_Framework_TestCase
{
    /** @var MapInterface */
    protected $map;

    public function setUp()
    {
        $this->map = new Map();
    }

    public function testShouldImplementsInterfaces()
    {
        $this->assertInstanceOf(MapInterface::class, $this->map);
        $this->assertInstanceOf(CollectionInterface::class, $this->map);
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
        $map = Map::createFromArray($array, $recursive);

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

        $map = Map::createFromArray($array, $recursive);

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

    public function testShouldThrowBadMathodCallExceptionOnAddItemsToMapArrayWay()
    {
        $this->setExpectedException(\BadMethodCallException::class);

        $this->map['key'] = 'value';
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMap($key, $value)
    {
        $newMap = $this->map->set($key, $value);

        $this->assertNotSame($this->map, $newMap);
        $this->assertEquals($value, $newMap->get($key));
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
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->map->set($key, 'value');
    }

    /**
     * @param object|array $key
     *
     * @dataProvider invalidKeyProvider
     */
    public function testShouldThrowBadMethodCallExceptionOnAddingObject($key)
    {
        $this->setExpectedException(\BadMethodCallException::class);

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
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

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
        $map = Map::createFromArray($array);

        $this->assertCount($originalCount, $map);

        $newMap = $map->set('key', 'value');
        $this->assertCount($originalCount, $map);
        $this->assertCount($originalCount + 1, $newMap);
    }

    public function testShouldHasKeys()
    {
        $keyExists = 'has-key';
        $keyDoesNotExist = 'has-no-key';

        $this->map = $this->map->set($keyExists, 'value');

        $this->assertArrayHasKey($keyExists, $this->map);
        $this->assertArrayNotHasKey($keyDoesNotExist, $this->map);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesNotExist));
    }

    public function testShouldRemoveItem()
    {
        $key = 'key';
        $key2 = 'key2';

        $this->map = $this->map->set($key, 'value');
        $this->assertTrue($this->map->containsKey($key));

        $this->map = $this->map->set($key2, 'value2');
        $this->assertTrue($this->map->containsKey($key2));

        $newMap = $this->map->remove($key);
        $this->assertTrue($this->map->containsKey($key));
        $this->assertFalse($newMap->containsKey($key));
    }

    public function testShouldThrowBadMethodCallExceptionOnUnsetValueArrayWay()
    {
        $this->setExpectedException(\BadMethodCallException::class);

        $this->map = $this->map->set('key', 'value');

        unset($this->map['key']);
    }

    public function testShouldContainsValue()
    {
        $key = 'key';
        $value = 1;
        $valueNotPresented = 4;

        $this->map = $this->map->set($key, $value);

        $this->assertTrue($this->map->contains($value));
        $this->assertFalse($this->map->contains($valueNotPresented));
    }

    public function testShouldForeachItemInMap()
    {
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

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
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

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
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

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

    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToEach()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->map->each(1);
    }

    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToMap()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->map->map(1);
    }

    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToFilter()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->map->filter(1);
    }

    public function testShouldGetKeys()
    {
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

        $keys = $map->keys();

        $this->assertInstanceOf(ListInterface::class, $keys);
        $this->assertEquals([1, 2, 'three'], $keys->toArray());
    }

    public function testShouldGetValueArrayWay()
    {
        $this->map = $this->map->set('key', 'value');

        $this->assertEquals('value', $this->map['key']);
        $this->assertEquals('value', $this->map->get('key'));
    }

    public function testShouldGetValues()
    {
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

        $values = $map->values();

        $this->assertInstanceOf(ListInterface::class, $values);
        $this->assertEquals(['one', 'two', 3], $values->toArray());
    }

    public function testShouldCallReducerCorrectly()
    {
        $this->map = $this->map->set('key', 'value');

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
            $this->map = $this->map->set($key, $value);
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
            $this->map = $this->map->set($key, $value);
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

    public function testShouldGetImmutableMapAsMutable()
    {
        $this->map = $this->map->set('key', 'value');

        $mutable = $this->map->asMutable();

        $this->assertInstanceOf(\MF\Collection\MapInterface::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\Map::class, $mutable);

        $this->assertEquals($this->map->toArray(), $mutable->toArray());
    }

    public function testShouldClearCollection()
    {
        $this->map = $this->map->set('key', 'value');
        $this->assertTrue($this->map->contains('value'));

        $this->map->clear();
        $this->assertFalse($this->map->contains('value'));
    }
}
