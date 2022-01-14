<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\BadMethodCallException;
use MF\Collection\Exception\CollectionExceptionInterface;
use MF\Collection\ICollection;

class MapTest extends AbstractTestCase
{
    /** @var IMap */
    protected $map;

    protected function setUp(): void
    {
        $this->map = new Map();
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(IMap::class, $this->map);
        $this->assertInstanceOf(ICollection::class, $this->map);
        $this->assertInstanceOf(\ArrayAccess::class, $this->map);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->map);
        $this->assertInstanceOf(\Countable::class, $this->map);
    }

    /** @dataProvider arrayProvider */
    public function testShouldCreateMapFromArray(array $array, bool $recursive): void
    {
        $map = Map::from($array, $recursive);

        $this->assertEquals($array, $map->toArray());
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
                'array' => [1, 'value', 3, ['val', 4], ['array' => [5, 6]]],
                'recursive' => true,
            ],
            [
                'array' => [1, 'value', 3, ['val', 4], ['array' => [5, 6]]],
                'recursive' => false,
            ],
        ];
    }

    /** @dataProvider recursiveProvider */
    public function testShouldCreateMapFromArrayWithSubArray(bool $recursive): void
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

    public function recursiveProvider(): array
    {
        return [
            ['recursive' => false],
            ['recursive' => true],
        ];
    }

    public function testShouldCreateMapByCallback(): void
    {
        $map = Map::create(
            explode(',', '1, 2, 3'),
            function ($value) {
                return (int) $value;
            }
        );

        $this->assertSame([1, 2, 3], $map->toArray());
    }

    public function testShouldThrowBadMethodCallExceptionOnAddItemsToMapArrayWay(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Immutable map cannot be used as array to set value. Use set() method instead.');

        $this->map['key'] = 'value';
    }

    /** @dataProvider addItemsProvider */
    public function testShouldAddItemsToMap(mixed $key, mixed $value): void
    {
        $newMap = $this->map->set($key, $value);

        $this->assertNotSame($this->map, $newMap);
        $this->assertEquals($value, $newMap->get($key));
    }

    public function addItemsProvider(): array
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
                'key' => '24.23',
                'value' => 24.12,
            ],
        ];
    }

    /** @dataProvider invalidKeyProvider */
    public function testShouldThrowInvalidArgumentExceptionOnAddingObjectArrayWay(object|array $key, string $expectedMessage): void
    {
        $this->expectException(CollectionExceptionInterface::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->map->set($key, 'value');
    }

    /** @dataProvider invalidKeyProvider */
    public function testShouldThrowBadMethodCallExceptionOnAddingObject(object|array $key): void
    {
        $this->expectException(CollectionExceptionInterface::class);
        $this->expectExceptionMessage('Immutable map cannot be used as array to set value. Use set() method instead.');

        $this->map[$key] = 'value';
    }

    public function invalidKeyProvider(): array
    {
        return [
            // key, expectedMessage
            [new \stdClass(), 'Key cannot be an Object'],
            [[], 'Key cannot be an Array'],
        ];
    }

    public function testShouldIterateThroughMap(): void
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
     * @dataProvider arrayProvider
     */
    public function testShouldGetCount(array $array): void
    {
        $originalCount = count($array);
        $map = Map::from($array);

        $this->assertCount($originalCount, $map);

        $newMap = $map->set('key', 'value');
        $this->assertCount($originalCount, $map);
        $this->assertCount($originalCount + 1, $newMap);
    }

    public function testShouldHasKeys(): void
    {
        $keyExists = 'has-key';
        $keyDoesNotExist = 'has-no-key';

        $this->map = $this->map->set($keyExists, 'value');

        $this->assertArrayHasKey($keyExists, $this->map);
        $this->assertArrayNotHasKey($keyDoesNotExist, $this->map);

        $this->assertTrue($this->map->containsKey($keyExists));
        $this->assertFalse($this->map->containsKey($keyDoesNotExist));
    }

    public function testShouldRemoveItem(): void
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

    public function testShouldThrowBadMethodCallExceptionOnUnsetValueArrayWay(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->map = $this->map->set('key', 'value');

        unset($this->map['key']);
    }

    public function testShouldContainsValue(): void
    {
        $key = 'key';
        $value = 1;
        $valueNotPresented = 4;

        $this->map = $this->map->set($key, $value);

        $this->assertTrue($this->map->contains($value));
        $this->assertFalse($this->map->contains($valueNotPresented));
    }

    public function testShouldContainsValueBy(): void
    {
        $key = 'key';
        $value = 1;
        $valueNotPresented = 4;

        $this->map = $this->map->set($key, $value);

        $this->assertTrue($this->map->containsBy($this->findByKeyOrValue($key)));
        $this->assertTrue($this->map->containsBy($this->findByKeyOrValue($value)));
        $this->assertFalse($this->map->containsBy($this->findByKeyOrValue($valueNotPresented)));
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

    public function testShouldMapItemsToNewMap(): void
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

    public function testShouldFilterMapToNewMap(): void
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

    public function testShouldGetKeys(): void
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $keys = $map->keys();

        $this->assertInstanceOf(IList::class, $keys);
        $this->assertEquals([1, 2, 'three'], $keys->toArray());
    }

    public function testShouldGetValueArrayWay(): void
    {
        $this->map = $this->map->set('key', 'value');

        $this->assertEquals('value', $this->map['key']);
        $this->assertEquals('value', $this->map->get('key'));
    }

    public function testShouldGetValues(): void
    {
        $map = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);

        $values = $map->values();

        $this->assertInstanceOf(IList::class, $values);
        $this->assertEquals(['one', 'two', 3], $values->toArray());
    }

    public function testShouldCallReducerCorrectly(): void
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

    /** @dataProvider reduceProvider */
    public function testShouldReduceMap(callable $reducer, array $values, mixed $expected): void
    {
        foreach ($values as $key => $value) {
            $this->map = $this->map->set($key, $value);
        }

        $this->assertEquals($expected, $this->map->reduce($reducer));
    }

    public function reduceProvider(): array
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

    /** @dataProvider reduceInitialProvider */
    public function testShouldReduceMapWithInitialValue(callable $reducer, array $values, mixed $initialValue, mixed $expected): void
    {
        foreach ($values as $key => $value) {
            $this->map = $this->map->set($key, $value);
        }

        $this->assertEquals($expected, $this->map->reduce($reducer, $initialValue));
    }

    public function reduceInitialProvider(): array
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

    public function testShouldGetImmutableMapAsMutable(): void
    {
        $this->map = $this->map->set('key', 'value');

        $mutable = $this->map->asMutable();

        $this->assertInstanceOf(\MF\Collection\IMap::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\Map::class, $mutable);

        $this->assertEquals($this->map->toArray(), $mutable->toArray());
    }

    public function testShouldClearCollection(): void
    {
        $this->map = $this->map->set('key', 'value');
        $this->assertTrue($this->map->contains('value'));

        $this->map = $this->map->clear();
        $this->assertFalse($this->map->contains('value'));
    }

    public function testShouldCheckIfCollectionIsEmpty(): void
    {
        $this->map = $this->map->set('key', 'value');
        $this->assertFalse($this->map->isEmpty());

        $this->map = $this->map->clear();
        $this->assertTrue($this->map->isEmpty());
    }
}
