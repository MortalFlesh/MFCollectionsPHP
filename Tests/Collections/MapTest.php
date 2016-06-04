<?php

namespace MFCollections\Tests\Collections;

use MFCollections\Collections\CollectionInterface;
use MFCollections\Collections\ListInterface;
use MFCollections\Collections\Map;
use MFCollections\Collections\MapInterface;

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
     *
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnAddingObjectArrayWay($key)
    {
        $this->map->set($key, 'value');
    }

    /**
     * @param object|array $key
     *
     * @dataProvider invalidKeyProvider
     *
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnAddingObject($key)
    {
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
        $keyDoesntExist = 'has-no-key';

        $this->map->set($keyExists, 'value');

        $this->assertArrayHasKey($keyExists, $this->map);
        $this->assertArrayNotHasKey($keyDoesntExist, $this->map);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesntExist));
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
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

        $map->each(function ($key, $value) {
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToEach()
    {
        $this->map->each(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToMap()
    {
        $this->map->map(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToFilter()
    {
        $this->map->filter(1);
    }

    public function testShouldGetKeys()
    {
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

        $keys = $map->keys();

        $this->assertInstanceOf(ListInterface::class, $keys);
        $this->assertEquals([1, 2, 'three'], $keys->toArray());
    }

    public function testShouldGetValues()
    {
        $map = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);

        $values = $map->values();

        $this->assertInstanceOf(ListInterface::class, $values);
        $this->assertEquals(['one', 'two', 3], $values->toArray());
    }
}
