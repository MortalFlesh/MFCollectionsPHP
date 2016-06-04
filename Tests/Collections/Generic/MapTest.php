<?php

namespace MFCollections\Tests\Collections\Generic;

use MFCollections\Collections\CollectionInterface;
use MFCollections\Collections\Generic\CollectionGenericInterface;
use MFCollections\Collections\Generic\ListCollection;
use MFCollections\Collections\Generic\Map;
use MFCollections\Collections\MapInterface;

class MapTest extends \PHPUnit_Framework_TestCase
{
    /** @var Map */
    private $map;

    public function setUp()
    {
        $this->map = new Map('string', 'int');
    }

    public function testShouldImplementsInterfaces()
    {
        $this->assertInstanceOf(MapInterface::class, $this->map);
        $this->assertInstanceOf(CollectionInterface::class, $this->map);
        $this->assertInstanceOf(CollectionGenericInterface::class, $this->map);
        $this->assertInstanceOf(\ArrayAccess::class, $this->map);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->map);
        $this->assertInstanceOf(\Countable::class, $this->map);
    }

    public function testShouldThrowExceptionWhenBadCreateFunctionIsUsed()
    {
        $this->setExpectedException(\BadMethodCallException::class);
        
        Map::createFromArray([]);
    }
    
    public function testShouldThrowExceptionWhenBadCreateGenericFunctionIsUsed()
    {
        $this->setExpectedException(\BadMethodCallException::class);
        
        Map::createGenericListFromArray('string', []);
    }

    public function testShouldCreateMapFromArray()
    {
        $array = ['key' => 1, 'key2' => 2];
        $map = Map::createGenericFromArray('string', 'int', $array);

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals($array, $map->toArray());
    }

    public function testShouldThrowExceptionWhenCreateMapFromArrayWithBadType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $array = ['key' => 1, 'key2' => 2];
        $map = Map::createGenericFromArray('int', 'int', $array);

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals($array, $map->toArray());
    }

    /**
     * @param string $keyType
     * @param string $valueType
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidTypesProvider
     */
    public function testShouldNotCreateGenericMap($keyType, $valueType)
    {
        new Map($keyType, $valueType);
    }

    public function invalidTypesProvider()
    {
        return [
            [
                'keyType' => 'object',
                'valueType' => 'int',
            ],
            [
                'keyType' => 'array',
                'valueType' => 'string',
            ],
            [
                'keyType' => '',
                'valueType' => 'string',
            ],
            [
                'keyType' => 'float',
                'valueType' => '',
            ],
            [
                'keyType' => 'float',
                'valueType' => '',
            ],
            [
                'keyType' => 'mixed',
                'valueType' => 'array',
            ],
            [
                'keyType' => 'int',
                'valueType' => 'mixed',
            ],
            [
                'keyType' => 'instance_of_' . Map::class,
                'valueType' => 'mixed',
            ],
            [
                'keyType' => 'string',
                'valueType' => 'instance_of_',
            ],
            [
                'keyType' => 'string',
                'valueType' => 'instance_of_foo',
            ],
        ];
    }

    /**
     * @param string $keyType
     * @param string $valueType
     *
     * @dataProvider validTypesProvider
     */
    public function testShouldCreateGenericMap($keyType, $valueType)
    {
        $map = new Map($keyType, $valueType);

        $this->assertInstanceOf(Map::class, $map);
    }

    public function validTypesProvider()
    {
        return [
            [
                'keyType' => 'string',
                'valueType' => 'int',
            ],
            [
                'keyType' => 'int',
                'valueType' => 'bool',
            ],
            [
                'keyType' => 'string',
                'valueType' => 'array',
            ],
            [
                'keyType' => 'float',
                'valueType' => 'object',
            ],
            [
                'keyType' => 'string',
                'valueType' => 'instance_of_' . Map::class,
            ],
        ];
    }

    /**
     * @param string $key
     * @param int $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMapArrayWay($key, $value)
    {
        $this->map[$key] = $value;

        $this->assertEquals($value, $this->map[$key]);
    }

    /**
     * @param string $key
     * @param int $value
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
                'value' => 2,
            ],
            [
                'key' => 'string',
                'value' => -10,
            ],
        ];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidParamTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnBadTypeSet($key, $value)
    {
        $this->map->set($key, $value);
    }

    public function invalidParamTypesProvider()
    {
        return [
            [
                'key' => 'string',
                'value' => 'string',
            ],
            [
                'key' => [],
                'value' => 'string',
            ],
            [
                'key' => 1,
                'value' => false,
            ],
            [
                'key' => 'string',
                'value' => false,
            ],
            [
                'key' => 'string',
                'value' => 24.2,
            ],
            [
                'key' => 'string',
                'value' => [],
            ],
        ];
    }

    public function testShouldContainsKey()
    {
        $keyExists = 'key';
        $keyDoesntExist = 'keyNotIn';

        $this->map->set($keyExists, 1);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesntExist));
    }

    /**
     * @param string $key
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidKeyTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnContainsKeyWithInvalidType($key)
    {
        $this->map->containsKey($key);
    }

    public function invalidKeyTypesProvider()
    {
        return [
            ['key' => 1],
            ['key' => true],
            ['key' => []],
            ['key' => null],
            ['key' => 2.5],
            ['key' => new \stdClass()],
        ];
    }

    public function testShouldContainsValue()
    {
        $valueExists = 1;
        $valueDoesntExist = 2;

        $this->map->set('key', $valueExists);

        $this->assertTrue($this->map->contains($valueExists));
        $this->assertFalse($this->map->contains($valueDoesntExist));
    }

    /**
     * @param int $value
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidValueTypeProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnContainsValueWithInvalidType($value)
    {
        $this->map->contains($value);
    }

    public function invalidValueTypeProvider()
    {
        return [
            ['value' => ''],
            ['value' => 2.5],
            ['value' => false],
            ['value' => []],
            ['value' => new \stdClass()],
            ['value' => null],
        ];
    }

    public function testShouldRemoveValueFromMap()
    {
        $key = 'key';
        $this->assertFalse($this->map->containsKey($key));

        $this->map->set($key, 2);
        $this->assertTrue($this->map->containsKey($key));

        $this->map->remove($key);
        $this->assertFalse($this->map->containsKey($key));
    }

    /**
     * @param string $key
     *
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider invalidKeyTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnRemoveInvalidKeyType($key)
    {
        $this->map->remove($key);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowExceptionWhenForeachItemInMapWithArrowFunction()
    {
        $this->map->each('($k, $v) => {}');
    }

    public function testShouldMapToNewMapWithSameGenericType()
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);
        $this->map->set('key3', 3);

        $newMap = $this->map->map('($k, $v) => $v + 1');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key' => 2, 'key2' => 3, 'key3' => 4], $newMap->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionWhenMapFunctionReturnsBadType()
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $this->map->map('($k, $v) => $k . $v');
    }

    public function testShouldFilterItemsToNewMapByArrowFunction()
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);
        $this->map->set('key3', 3);

        $newMap = $this->map->filter('($k, $v) => $v > 1');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key2' => 2, 'key3' => 3], $newMap->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionAfterFilterItemsToNewMapByArrowFunction()
    {
        $newMap = $this->map->filter('($k, $v) => true');

        $newMap->set(1, '');
    }

    public function testShouldCombineMapAndFilterToCreateNewMap()
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $newMap = $this->map
            ->filter('($k, $v) => $v > 1')
            ->map('($k, $v) => $v * 3');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key2' => 6], $newMap->toArray());
    }

    public function testShouldGetKeysInGenericList()
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $keys = $this->map->keys();
        $this->assertInstanceOf(ListCollection::class, $keys);
        $this->assertEquals(['key', 'key2'], $keys->toArray());
    }
    
    public function testShouldGetValuesInGenericList()
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $values = $this->map->values();
        $this->assertInstanceOf(ListCollection::class, $values);
        $this->assertEquals([1, 2], $values->toArray());
    }
}
