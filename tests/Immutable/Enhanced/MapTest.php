<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Enhanced;

class MapTest extends \MF\Collection\Immutable\MapTest
{
    /** @var Map */
    private $mapEnhanced;

    protected function setUp(): void
    {
        $this->map = new Map();
        $this->mapEnhanced = Map::from([1 => 'one', 'two' => 'two', 'three' => 3]);
    }

    public function testShouldCreateMapByCallback(): void
    {
        $map = Map::create(
            explode(',', '1, 2, 3'),
            '($v) => (int) $v'
        );

        $this->assertSame([1, 2, 3], $map->toArray());
    }

    public function testShouldMapCollectionToNewMapByArrowFunction(): void
    {
        $newMap = $this->mapEnhanced->map('($k, $v) => $k . $v');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals([1 => '1one', 'two' => 'twotwo', 'three' => 'three3'], $newMap->toArray());
    }

    public function testShouldFilterItemsToNewMapByArrowFunction(): void
    {
        $newMap = $this->mapEnhanced->filter('($k, $v) => $v >= 1');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals(['three' => 3], $newMap->toArray());
    }

    public function testShouldCombineMapAndFilterToCreateNewMap(): void
    {
        $newMap = $this->mapEnhanced
            ->filter('($k, $v) => $k === "two"')
            ->map('($k, $v) => $k . $v');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals(['two' => 'twotwo'], $newMap->toArray());
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
            $this->mapEnhanced = $this->mapEnhanced->set($key, $value);
        }

        $this->assertEquals($expected, $this->mapEnhanced->reduce($reducer));
    }

    public function reduceByArrowFunctionProvider()
    {
        return [
            'total count' => [
                '($total, $current) => $total + $current',
                [
                    'one' => 1,
                    'two' => 2,
                    'three' => 3,
                    'four' => 4,
                    'five' => 5,
                ],
                15,
            ],
            'concat strings with keys' => [
                '($total, $current, $key, $map) => $total . $current . "_" . $key . "|"',
                [
                    1 => 'one',
                    2 => 'two',
                    3 => 'three',
                ],
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
    public function testShouldReduceMapWithInitialValueByArrowFunction(
        $reducer,
        array $values,
        $initialValue,
        $expected
    ): void {
        $this->mapEnhanced = new Map();

        foreach ($values as $key => $value) {
            $this->mapEnhanced = $this->mapEnhanced->set($key, $value);
        }

        $this->assertEquals($expected, $this->mapEnhanced->reduce($reducer, $initialValue));
    }

    public function reduceInitialByArrowFunctionProvider()
    {
        return [
            // reducer, values, initalValue, result
            'total count' => [
                '($total, $current) => $total + $current',
                [
                    'one' => 1,
                    'two' => 2,
                    'three' => 3,
                    'four' => 4,
                    'five' => 5,
                ],
                10,
                25,
            ],
            'total count with empty map' => [
                '($total, $current) => $total + $current',
                [],
                10,
                10,
            ],
            'concat strings with keys' => [
                '($total, $current, $key, $map) => $total . $current . "_" . $key . "|"',
                [
                    1 => 'one',
                    2 => 'two',
                    3 => 'three',
                ],
                'initial-',
                'initial-one_1|two_2|three_3|',
            ],
        ];
    }

    public function testShouldGetMutableEnhancedListAsImmutableEnhanced(): void
    {
        $this->mapEnhanced->set('key', 'value');

        $mutable = $this->mapEnhanced->asMutable();

        $this->assertInstanceOf(\MF\Collection\IMap::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\Enhanced\Map::class, $mutable);

        $this->assertEquals($this->mapEnhanced->toArray(), $mutable->toArray());
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
