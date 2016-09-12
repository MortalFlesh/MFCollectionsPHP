<?php

namespace MF\Tests\Collection\Immutable\Generic;

use MF\Collection\CollectionInterface as BaseCollectionInterface;
use MF\Collection\Generic\CollectionInterface;
use MF\Collection\Immutable\Generic\ListCollection;
use MF\Collection\Immutable\Generic\Map;
use MF\Collection\Immutable\MapInterface;
use MF\Collection\MapInterface as BaseMapInterface;
use MF\Tests\Fixtures\EntityInterface;
use MF\Tests\Fixtures\SimpleEntity;

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
        $this->assertInstanceOf(BaseMapInterface::class, $this->map);
        $this->assertInstanceOf(BaseCollectionInterface::class, $this->map);
        $this->assertInstanceOf(CollectionInterface::class, $this->map);
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
     * @dataProvider invalidTypesProvider
     */
    public function testShouldNotCreateGenericMap($keyType, $valueType)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

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
                'keyType' => Map::class,
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
                'valueType' => Map::class,
            ],
        ];
    }

    /**
     * @param string $key
     * @param int $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMap($key, $value)
    {
        $this->map = $this->map->set($key, $value);

        $this->assertInstanceOf(Map::class, $this->map);
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

    public function testShouldThrowBadMathodCallExceptionOnAddItemsToMapArrayWay()
    {
        $this->setExpectedException(\BadMethodCallException::class);

        $this->map['key'] = 'value';
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @dataProvider invalidParamTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnBadTypeSet($key, $value)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

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
        $keyDoesNotExist = 'keyNotIn';

        $this->map = $this->map->set($keyExists, 1);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesNotExist));
    }

    /**
     * @param string $key
     *
     * @dataProvider invalidKeyTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnContainsKeyWithInvalidType($key)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

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
        $valueDoesNotExist = 2;

        $this->map = $this->map->set('key', $valueExists);

        $this->assertTrue($this->map->contains($valueExists));
        $this->assertFalse($this->map->contains($valueDoesNotExist));
    }

    /**
     * @param int $value
     *
     * @dataProvider invalidValueTypeProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnContainsValueWithInvalidType($value)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

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

        $this->map = $this->map->set($key, 2);
        $this->assertTrue($this->map->containsKey($key));

        $this->map = $this->map->remove($key);
        $this->assertFalse($this->map->containsKey($key));
    }

    /**
     * @param string $key
     *
     * @dataProvider invalidKeyTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnRemoveInvalidKeyType($key)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->map->remove($key);
    }

    public function testShouldThrowExceptionWhenForeachItemInMapWithArrowFunction()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->map->each('($k, $v) => {}');
    }

    public function testShouldMapToNewMapWithSameGenericType()
    {
        $this->map = $this->map->set('key', 1);
        $this->map = $this->map->set('key2', 2);
        $this->map = $this->map->set('key3', 3);

        $newMap = $this->map->map('($k, $v) => $v + 1');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key' => 2, 'key2' => 3, 'key3' => 4], $newMap->toArray());
    }

    public function testShouldMapToNewMap()
    {
        $map = new Map('string', EntityInterface::class);
        $map = $map->set('one', new SimpleEntity(1));
        $map = $map->set('two', new SimpleEntity(2));

        $newMap = $map->map('($k, $v) => $v->getId()');

        $this->assertNotSame($map, $newMap);

        $this->assertInstanceOf(\MF\Collection\Immutable\Enhanced\Map::class, $newMap);
        $this->assertEquals(['one' => 1, 'two' => 2], $newMap->toArray());
    }

    public function testShouldMapToNewGenericMap()
    {
        $map = new Map('string', EntityInterface::class);
        $map = $map->set('one', new SimpleEntity(1));
        $map = $map->set('two', new SimpleEntity(2));

        $newMap = $map->map('($k, $v) => $v->getId()', 'int');

        $this->assertNotSame($map, $newMap);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals(['one' => 1, 'two' => 2], $newMap->toArray());
    }

    public function testShouldFilterItemsToNewMapByArrowFunction()
    {
        $this->map = $this->map->set('key', 1);
        $this->map = $this->map->set('key2', 2);
        $this->map = $this->map->set('key3', 3);

        $newMap = $this->map->filter('($k, $v) => $v > 1');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key2' => 2, 'key3' => 3], $newMap->toArray());
    }

    public function testShouldThrowInvalidArgumentExceptionAfterFilterItemsToNewMapByArrowFunction()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $newMap = $this->map->filter('($k, $v) => true');

        $newMap->set(1, '');
    }

    public function testShouldCombineMapAndFilterToCreateNewMap()
    {
        $this->map = $this->map->set('key', 1);
        $this->map = $this->map->set('key2', 2);

        $newMap = $this->map
            ->filter('($k, $v) => $v > 1')
            ->map('($k, $v) => $v * 3');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key2' => 6], $newMap->toArray());
    }

    public function testShouldGetKeysInGenericList()
    {
        $this->map = $this->map->set('key', 1);
        $this->map = $this->map->set('key2', 2);

        $keys = $this->map->keys();
        $this->assertInstanceOf(ListCollection::class, $keys);
        $this->assertEquals(['key', 'key2'], $keys->toArray());
    }

    public function testShouldGetValuesInGenericList()
    {
        $this->map = $this->map->set('key', 1);
        $this->map = $this->map->set('key2', 2);

        $values = $this->map->values();
        $this->assertInstanceOf(ListCollection::class, $values);
        $this->assertEquals([1, 2], $values->toArray());
    }

    public function testShouldReduceMap()
    {
        $this->map = $this->map->set('key', 1);
        $this->map = $this->map->set('key2', 2);
        $this->map = $this->map->set('key3', 3);

        $this->assertEquals(6, $this->map->reduce('($t, $c) => $t + $c'));
    }

    public function testShouldGetImmutableGenericMapAsMutableGenericMap()
    {
        $this->map = $this->map->set('key', 666);

        $mutable = $this->map->asMutable();

        $this->assertInstanceOf(\MF\Collection\Mutable\MapInterface::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\Generic\Map::class, $mutable);

        $this->assertEquals($this->map->toArray(), $mutable->toArray());
    }

    public function testShouldReduceMapWithInitialValue()
    {
        $map = new Map('string', 'int');
        $map = $map->set('one', 1);
        $map = $map->set('two', 2);
        $map = $map->set('three', 3);

        $this->assertEquals(10 + 1 + 2 + 3, $map->reduce('($t, $v) => $t + $v', 10));
    }
}
