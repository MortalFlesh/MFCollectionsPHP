<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Enhanced;

class MapTest extends \MF\Collection\Mutable\MapTest
{
    /** @var Map */
    protected $mapEnhanced;

    protected function setUp(): void
    {
        $this->map = new Map();
        $this->mapEnhanced = Map::from([1 => 'one', 2 => 'two', 'three' => 3]);
    }

    public function testShouldCreateMapByCallback(): void
    {
        $map = Map::create(
            explode(',', '1, 2, 3'),
            '($v) => (int) $v'
        );

        $this->assertSame([1, 2, 3], $map->toArray());
    }

    public function testShouldMapToNewMapByArrowFunction(): void
    {
        $newMap = $this->mapEnhanced->map('($k, $v) => $k . $v');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals([1 => '1one', 2 => '2two', 'three' => 'three3'], $newMap->toArray());
    }

    public function testShouldFilterItemsToNewMapByArrowFunction(): void
    {
        $newMap = $this->mapEnhanced->filter('($k, $v) => $k >= 1');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals([1 => 'one', 2 => 'two'], $newMap->toArray());
    }

    public function testShouldCombineMapAndFilterToCreateNewMap(): void
    {
        $newMap = $this->mapEnhanced
            ->filter('($k, $v) => $k >= 1')
            ->map('($k, $v) => $k . $v');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals([1 => '1one', 2 => '2two'], $newMap->toArray());
    }

    /**
     * @param callable|string $reducer
     * @param array $values
     * @param mixed $expected
     *
     * @dataProvider reduceByArrowFunctionProvider
     */
    public function testShouldReduceListByArrowFunction($reducer, array $values, $expected): void
    {
        $this->mapEnhanced = new Map();

        foreach ($values as $key => $value) {
            $this->mapEnhanced->set($key, $value);
        }

        $this->assertEquals($expected, $this->mapEnhanced->reduce($reducer));
    }

    public function reduceByArrowFunctionProvider()
    {
        return [
            'total count' => [
                '($total, $current) => $total + $current',
                ['one' => 1, 'two' => 2, 'three' => 3],
                6,
            ],
            'concat strings with indexes' => [
                '($total, $current, $index, $map) => $total . $current . "_" . $index . "|"',
                [1 => 'one', 2 => 'two', 3 => 'three'],
                'one_1|two_2|three_3|',
            ],
        ];
    }

    /**
     * @param callable|string $reducer
     * @param array $values
     * @param mixed $initialValue
     * @param mixed $expected
     *
     * @dataProvider reduceInitialByArrowFunctionProvider
     */
    public function testShouldReduceListWithInitialValueByArrowFunction(
        $reducer,
        array $values,
        $initialValue,
        $expected
    ): void {
        $this->mapEnhanced = new Map();

        foreach ($values as $key => $value) {
            $this->mapEnhanced->set($key, $value);
        }

        $this->assertEquals($expected, $this->mapEnhanced->reduce($reducer, $initialValue));
    }

    public function reduceInitialByArrowFunctionProvider()
    {
        return [
            'total count' => [
                '($total, $current) => $total + $current',
                ['one' => 1, 'two' => 2, 'three' => 3],
                10,
                16,
            ],
            'total count with empty map' => [
                '($total, $current) => $total + $current',
                [],
                10,
                10,
            ],
            'concat strings with indexes' => [
                '($total, $current, $index, $map) => $total . $current . "_" . $index . "|"',
                [1 => 'one', 2 => 'two', 3 => 'three'],
                'initial-',
                'initial-one_1|two_2|three_3|',
            ],
        ];
    }

    public function testShouldGetMutableEnhancedMapAsImmutableEnhancedMap(): void
    {
        $this->map->set('key', 'value');

        $immutable = $this->map->asImmutable();

        $this->assertInstanceOf(\MF\Collection\Immutable\IMap::class, $immutable);
        $this->assertInstanceOf(\MF\Collection\Immutable\Enhanced\Map::class, $immutable);

        $this->assertEquals($this->map->toArray(), $immutable->toArray());
    }

    public function testShouldContainsValueByArrowFunction(): void
    {
        $key = 'key';
        $value = 1;
        $valueNotPresented = 4;

        $this->map->set($key, $value);

        $this->assertTrue($this->map->containsBy('($k, $v) => $v === ' . $value));
        $this->assertFalse($this->map->containsBy('($k, $v) => $v === ' . $valueNotPresented));
    }

    public function testShouldClearCollection(): void
    {
        $this->map->set('key', 'value');
        $this->assertTrue($this->map->contains('value'));

        $this->map->clear();
        $this->assertFalse($this->map->contains('value'));
    }

    public function testShouldCheckIfCollectionIsEmpty(): void
    {
        $this->map->set('key', 'value');
        $this->assertFalse($this->map->isEmpty());

        $this->map->clear();
        $this->assertTrue($this->map->isEmpty());
    }
}
