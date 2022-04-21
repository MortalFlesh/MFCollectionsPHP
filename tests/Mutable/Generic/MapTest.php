<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Fixtures\SimpleEntity;
use MF\Collection\Immutable\Generic\KVPair;
use MF\Collection\Immutable\Tuple;

class MapTest extends AbstractTestCase
{
    /** @phpstan-var Map<int|string, mixed> */
    private Map $map;

    protected function setUp(): void
    {
        $this->map = new Map();
    }

    public function testShouldTransformMapToSeq(): void
    {
        $map = Map::from(['one' => 1, 'two' => 2]);

        $result = $map->toSeq();

        $map->set('three', 3);

        $expected = [
            Tuple::of('one', 1),
            Tuple::of('two', 2),
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(IMap::class, $this->map);
        $this->assertInstanceOf(ICollection::class, $this->map);
        $this->assertInstanceOf(\ArrayAccess::class, $this->map);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->map);
        $this->assertInstanceOf(\Countable::class, $this->map);
    }

    public function testShouldCreateMapFromArray(): void
    {
        $array = ['key' => 1, 'key2' => 2];
        $map = Map::from($array);

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals($array, $map->toArray());
    }

    public function testShouldCreateMapFromMixedArray(): void
    {
        $array = ['key' => 1, 2 => 'two'];
        $map = Map::from($array);

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals($array, $map->toArray());
    }

    /** @dataProvider validTypesProvider */
    public function testShouldCreateGenericMap(string $keyType, string $valueType): void
    {
        $map = new Map();

        $this->assertInstanceOf(Map::class, $map);
    }

    public function validTypesProvider(): array
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
        $map = Map::create(
            explode(',', '1,2,3'),
            fn ($value) => new SimpleEntity((int) $value)
        );

        $map->map(fn (SimpleEntity $e) => $e->getId());

        $this->assertSame([1, 2, 3], $map->toArray());
    }

    /** @dataProvider addItemsProvider */
    public function testShouldAddItemsToMapArrayWay(string $key, int $value): void
    {
        $this->map[$key] = $value;

        $this->assertEquals($value, $this->map[$key]);
    }

    /** @dataProvider addItemsProvider */
    public function testShouldAddItemsToMap(string $key, int $value): void
    {
        $this->map->set($key, $value);

        $this->assertEquals($value, $this->map->get($key));
    }

    public function addItemsProvider(): array
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

    public function testShouldContainsKey(): void
    {
        $keyExists = 'key';
        $keyDoesNotExist = 'keyNotIn';

        $this->map->set($keyExists, 1);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesNotExist));
    }

    public function testShouldContainsKeyByArrayAccess(): void
    {
        $keyExists = 'key';
        $keyDoesNotExist = 'keyNotIn';

        $this->map->set($keyExists, 1);

        $this->assertTrue(isset($this->map[$keyExists]));
        $this->assertFalse(isset($this->map[$keyDoesNotExist]));
    }

    public function testShouldContainsValue(): void
    {
        $valueExists = 1;
        $valueDoesNotExist = 2;

        $this->map->set('key', $valueExists);

        $this->assertTrue($this->map->contains($valueExists));
        $this->assertFalse($this->map->contains($valueDoesNotExist));
    }

    public function testShouldContainsValueBy(): void
    {
        $valueExists = 1;
        $valueDoesNotExist = 2;

        $this->map->set('key', $valueExists);

        $this->assertTrue($this->map->containsBy(fn ($v) => $v === $valueExists));
        $this->assertFalse($this->map->containsBy(fn ($v) => $v === $valueDoesNotExist));
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

    public function testShouldMapToNewMapWithSameGenericType(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);
        $this->map->set('key3', 3);

        $this->map->map(fn ($v, $k) => $v + 1);

        $this->assertEquals(['key' => 2, 'key2' => 3, 'key3' => 4], $this->map->toArray());
    }

    public function testShouldMapToNewMap(): void
    {
        $map = new Map();
        $map->set('one', new SimpleEntity(1));
        $map->set('two', new SimpleEntity(2));

        $map->map(fn ($v) => $v->getId());

        $this->assertInstanceOf(\MF\Collection\Mutable\Generic\Map::class, $map);
        $this->assertEquals(['one' => 1, 'two' => 2], $map->toArray());
    }

    public function testShouldMapToNewGenericMap(): void
    {
        $map = new Map();
        $map->set('one', new SimpleEntity(1));
        $map->set('two', new SimpleEntity(2));

        $map->map(fn ($v) => $v->getId());

        $this->assertInstanceOf(Map::class, $map);
        $this->assertEquals(['one' => 1, 'two' => 2], $map->toArray());
    }

    public function testShouldFilterItemsToNewMapByArrowFunction(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);
        $this->map->set('key3', 3);

        $this->map->filter(fn ($v) => $v > 1);

        $this->assertEquals(['key2' => 2, 'key3' => 3], $this->map->toArray());
    }

    public function testShouldCombineMapAndFilterToCreateNewMap(): void
    {
        $this->map->set('key', 1);
        $this->map->set('key2', 2);

        $this->map->filter(fn ($v) => $v > 1);
        $this->map->map(fn ($v) => $v * 3);

        $this->assertEquals(['key2' => 6], $this->map->toArray());
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

        $this->assertEquals(6, $this->map->reduce(fn ($t, $c) => $t + $c));
    }

    public function testShouldGetMutableGenericMapAsImmutableGenericMap(): void
    {
        $this->map->set('key', 666);

        $immutable = $this->map->asImmutable();

        $this->assertInstanceOf(\MF\Collection\Immutable\Generic\IMap::class, $immutable);
        $this->assertInstanceOf(\MF\Collection\Immutable\Generic\Map::class, $immutable);

        $this->assertEquals($this->map->toArray(), $immutable->toArray());
    }

    public function testShouldReduceMapWithInitialValue(): void
    {
        $map = new Map();
        $map->set('one', 1);
        $map->set('two', 2);
        $map->set('three', 3);

        $this->assertEquals(10 + 1 + 2 + 3, $map->reduce(fn ($t, $v) => $t + $v, 10));
    }

    public function testShouldReduceListWithInitialValueToOtherType(): void
    {
        $map = new Map();
        $map->set('one', 1);
        $map->set('two', 2);
        $map->set('three', 3);

        $this->assertEquals('123', $map->reduce(fn ($t, $v) => $t . $v, ''));
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

    public function testShouldReduceAllGivenCallbacks(): void
    {
        $add = function ($a) {
            return function ($b) use ($a) {
                return $a + $b;
            };
        };

        $callbacks = Map::from([
            'trim' => 'trim',
            'toInt' => function ($input) {
                return (int) $input;
            },
            'increment' => $add(1),
        ]);

        $result = $callbacks->reduce(function ($result, callable $callback) {
            return $callback($result);
        }, '  10');

        $this->assertSame(11, $result);
    }

    /** @dataProvider providePairs */
    public function testShouldCreateMapFromPairs(iterable $pairs, array $expected): void
    {
        $map = Map::fromPairs($pairs);

        $this->assertSame($expected, $map->toArray());
    }

    public function providePairs(): array
    {
        return [
            // pairs, expected
            'empty' => [[], []],
            'arrays' => [
                [
                    ['one', 3],
                    ['two', 2],
                    ['one', 1],
                ],
                ['one' => 1, 'two' => 2],
            ],
            'kv pairs' => [
                [
                    new KVPair('one', 3),
                    new KVPair('two', 2),
                    new KVPair('one', 1),
                ],
                ['one' => 1, 'two' => 2],
            ],
            'tuples' => [
                [
                    Tuple::of('one', 3),
                    Tuple::of('two', 2),
                    Tuple::of('one', 1),
                ],
                ['one' => 1, 'two' => 2],
            ],
            'mix' => [
                [
                    ['one', 3],
                    Tuple::of('two', 2),
                    new KVPair('one', 1),
                ],
                ['one' => 1, 'two' => 2],
            ],
        ];
    }

    public function testShouldNotCreateMapFromInvalidPair(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Map::fromPairs(['invalid']);
    }

    public function testShouldFindValue(): void
    {
        $this->map->set('isThere', 'value');

        $this->assertSame('value', $this->map->find('isThere'));
        $this->assertNull($this->map->find('is Not There'));
    }

    public function testShouldNotGetValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->map->get('is Not There');
    }

    public function testShouldUnsetKeyByArrayAccess(): void
    {
        $this->map->set('key', 'value');

        $this->assertTrue($this->map->containsKey('key'));

        unset($this->map['key']);
        $this->assertFalse($this->map->containsKey('key'));
    }

    public function testShouldCountMap(): void
    {
        $map = Map::from(['one' => 1, 'two' => 2]);

        $this->assertCount(2, $map);
    }

    public function testShouldForeachItemInMap(): void
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $map->each(function ($value, $key): void {
            if ($key === 1) {
                $this->assertEquals('one', $value);
            } elseif ($key === 2) {
                $this->assertEquals('two', $value);
            } elseif ($key === 'three') {
                $this->assertEquals(3, $value);
            }
        });
    }

    public function testShouldCheckAllValues(): void
    {
        $map = Map::from([1 => 'one', 2 => 'two']);

        $this->assertTrue($map->forAll(is_string(...)));
        $this->assertFalse($map->forAll(is_int(...)));
    }

    public function testShouldImplodeValues(): void
    {
        $map = Map::from([1 => 'one', 2 => 'two']);

        $this->assertSame('one_two', $map->implode('_'));
    }

    public function testShouldFindKey(): void
    {
        $map = Map::from([1 => 'one', 2 => 'two']);

        $this->assertSame(1, $map->findKey('one'));
        $this->assertNull($map->findKey('not there'));
    }

    public function testShouldGetPairsFromMap(): void
    {
        $pairs = Map::from([1 => 'one', 2 => 'two'])->pairs();
        $expected = [
            new KVPair(1, 'one'),
            new KVPair(2, 'two'),
        ];

        $this->assertEquals($expected, $pairs->toArray());
    }

    public function testShouldTransformMapToList(): void
    {
        $list = Map::from([1 => 'one', 2 => 'two'])->toList();
        $expected = [
            Tuple::of(1, 'one'),
            Tuple::of(2, 'two'),
        ];

        $this->assertEquals($expected, $list->toArray());
    }

    public function testShouldTransformMapToPairsAndUseKeysOnly(): void
    {
        $pairs = Map::from(['one' => 1, 'two' => 2])
            ->pairs();

        $pairs->map(KVPair::key(...));

        $this->assertSame(['one', 'two'], $pairs->toArray());
    }
}
