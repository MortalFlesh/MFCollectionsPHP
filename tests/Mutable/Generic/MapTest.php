<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Fixtures\EntityInterface;
use MF\Collection\Fixtures\SimpleEntity;
use MF\Collection\Generic\ICollection;
use MF\Collection\Generic\IMap;
use MF\Collection\Mutable\ICollection as BaseCollectionInterface;
use MF\Collection\Mutable\IMap as BaseMapInterface;

class MapTest extends AbstractTestCase
{
    /** @var Map */
    private $map;

    protected function setUp(): void
    {
        $this->map = new Map('string', 'int');
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(IMap::class, $this->map);
        $this->assertInstanceOf(BaseMapInterface::class, $this->map);
        $this->assertInstanceOf(BaseCollectionInterface::class, $this->map);
        $this->assertInstanceOf(ICollection::class, $this->map);
        $this->assertInstanceOf(\ArrayAccess::class, $this->map);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->map);
        $this->assertInstanceOf(\Countable::class, $this->map);
    }

    public function testShouldThrowExceptionWhenBadCreateFunctionIsUsed(): void
    {
        $this->expectException(\BadMethodCallException::class);

        Map::from([]);
    }

    public function testShouldCreateMapFromArray(): void
    {
        $array = ['key' => 1, 'key2' => 2];
        $map = Map::fromKT('string', 'int', $array);

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals($array, $map->toArray());
    }

    public function testShouldCreateMapFromMixedArray(): void
    {
        $array = ['key' => 1, 2 => 'two'];
        $map = Map::fromKT('mixed', 'any', $array);

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals($array, $map->toArray());
    }

    public function testShouldThrowExceptionWhenCreateMapFromArrayWithBadType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $array = ['key' => 1, 'key2' => 2];
        $map = Map::fromKT('int', 'int', $array);

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals($array, $map->toArray());
    }

    /**
     * @param string $keyType
     * @param string $valueType
     *
     * @dataProvider invalidTypesProvider
     */
    public function testShouldNotCreateGenericMap($keyType, $valueType): void
    {
        $this->expectException(\InvalidArgumentException::class);

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
    public function testShouldCreateGenericMap($keyType, $valueType): void
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

    public function testShouldCreateMapByCallback(): void
    {
        $map = Map::createKT(
            'int',
            SimpleEntity::class,
            explode(',', '1,2,3'),
            function ($value) {
                return new SimpleEntity((int) $value);
            }
        );

        $map = $map->map('($k, $e) => $e->getId()', 'int');

        $this->assertSame([1, 2, 3], $map->toArray());
    }

    public function testShouldThrowBadMethodUseExceptionWhenCreatingMapByCallback(): void
    {
        $this->expectException(\BadMethodCallException::class);

        Map::create([], function ($v) {
            return $v;
        });
    }

    /**
     * @param string $key
     * @param int $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMapArrayWay($key, $value): void
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
    public function testShouldAddItemsToMap($key, $value): void
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
     * @dataProvider invalidParamTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnBadTypeSet($key, $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

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

    public function testShouldContainsKey(): void
    {
        $keyExists = 'key';
        $keyDoesNotExist = 'keyNotIn';

        $this->map->set($keyExists, 1);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesNotExist));
    }

    /**
     * @param string $key
     *
     * @dataProvider invalidKeyTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnContainsKeyWithInvalidType($key): void
    {
        $this->expectException(\InvalidArgumentException::class);

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

    public function testShouldContainsValue(): void
    {
        $valueExists = 1;
        $valueDoesNotExist = 2;

        $this->map->set('key', $valueExists);

        $this->assertTrue($this->map->contains($valueExists));
        $this->assertFalse($this->map->contains($valueDoesNotExist));
    }

    /**
     * @param int $value
     *
     * @dataProvider invalidValueTypeProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnContainsValueWithInvalidType($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

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

    public function testShouldRemoveValueFromMap(): void
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
     * @dataProvider invalidKeyTypesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionOnRemoveInvalidKeyType($key): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->map->remove($key);
    }

    public function testShouldMapToNewMapWithSameGenericType(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);
        $this->map->set('key3', 3);

        $newMap = $this->map->map('($k, $v) => $v + 1');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key' => 2, 'key2' => 3, 'key3' => 4], $newMap->toArray());
    }

    public function testShouldMapToNewMap(): void
    {
        $map = new Map('string', EntityInterface::class);
        $map->set('one', new SimpleEntity(1));
        $map->set('two', new SimpleEntity(2));

        $newMap = $map->map('($k, $v) => $v->getId()', 'int');

        $this->assertNotSame($map, $newMap);

        $this->assertInstanceOf(\MF\Collection\Mutable\Generic\Map::class, $newMap);
        $this->assertEquals(['one' => 1, 'two' => 2], $newMap->toArray());
    }

    public function testShouldMapToNewGenericMap(): void
    {
        $map = new Map('string', EntityInterface::class);
        $map->set('one', new SimpleEntity(1));
        $map->set('two', new SimpleEntity(2));

        $newMap = $map->map('($k, $v) => $v->getId()', 'int');

        $this->assertNotSame($map, $newMap);

        $this->assertInstanceOf(Map::class, $newMap);
        $this->assertEquals(['one' => 1, 'two' => 2], $newMap->toArray());
    }

    public function testShouldFilterItemsToNewMapByArrowFunction(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);
        $this->map->set('key3', 3);

        $newMap = $this->map->filter('($k, $v) => $v > 1');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key2' => 2, 'key3' => 3], $newMap->toArray());
    }

    public function testShouldThrowInvalidArgumentExceptionAfterFilterItemsToNewMapByArrowFunction(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $newMap = $this->map->filter('($k, $v) => true');

        $newMap->set(1, '');
    }

    public function testShouldCombineMapAndFilterToCreateNewMap(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $newMap = $this->map
            ->filter('($k, $v) => $v > 1')
            ->map('($k, $v) => $v * 3');

        $this->assertNotEquals($this->map, $newMap);
        $this->assertEquals(['key2' => 6], $newMap->toArray());
    }

    public function testShouldGetKeysInGenericList(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $keys = $this->map->keys();
        $this->assertInstanceOf(ListCollection::class, $keys);
        $this->assertEquals(['key', 'key2'], $keys->toArray());
    }

    public function testShouldGetValuesInGenericList(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $values = $this->map->values();
        $this->assertInstanceOf(ListCollection::class, $values);
        $this->assertEquals([1, 2], $values->toArray());
    }

    public function testShouldReduceMap(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);
        $this->map->set('key3', 3);

        $this->assertEquals(6, $this->map->reduce('($t, $c) => $t + $c'));
    }

    public function testShouldGetMutableGenericMapAsImmutableGenericMap(): void
    {
        $this->map->set('key', 666);

        $immutable = $this->map->asImmutable();

        $this->assertInstanceOf(\MF\Collection\Immutable\IMap::class, $immutable);
        $this->assertInstanceOf(\MF\Collection\Immutable\Generic\Map::class, $immutable);

        $this->assertEquals($this->map->toArray(), $immutable->toArray());
    }

    public function testShouldReduceMapWithInitialValue(): void
    {
        $map = new Map('string', 'int');
        $map->set('one', 1);
        $map->set('two', 2);
        $map->set('three', 3);

        $this->assertEquals(10 + 1 + 2 + 3, $map->reduce('($t, $v) => $t + $v', 10));
    }

    public function testShouldReduceListWithInitialValueToOtherType(): void
    {
        $map = new Map('string', 'int');
        $map->set('one', 1);
        $map->set('two', 2);
        $map->set('three', 3);

        $this->assertEquals('123', $map->reduce('($t, $v) => $t . $v', ''));
    }

    public function testShouldClearCollection(): void
    {
        $this->map->set('key', 123);
        $this->assertTrue($this->map->contains(123));

        $this->map->clear();
        $this->assertFalse($this->map->contains(123));
    }

    public function testShouldCheckIfCollectionIsEmpty(): void
    {
        $this->map->set('key', 123);
        $this->assertFalse($this->map->isEmpty());

        $this->map->clear();
        $this->assertTrue($this->map->isEmpty());
    }
}
