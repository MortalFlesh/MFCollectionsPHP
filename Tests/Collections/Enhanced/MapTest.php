<?php

namespace MFCollections\Tests\Collections\Enhanced;

use MFCollections\Collections\Enhanced\Map;

class MapTest extends \MFCollections\Tests\Collections\MapTest
{
    /** @var Map */
    protected $mapEnhanced;

    public function setUp()
    {
        $this->map = new Map();
        $this->mapEnhanced = Map::createFromArray([1 => 'one', 2 => 'two', 'three' => 3]);
    }

    public function testShouldThrowExceptionWhenForeachItemInMapWithArrowFunction()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->mapEnhanced->each('($k, $v) => {}');
    }

    public function testShouldMapToNewMapByArrowFunction()
    {
        $newMap = $this->mapEnhanced->map('($k, $v) => $k . $v');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals([1 => '1one', 2 => '2two', 'three' => 'three3'], $newMap->toArray());
    }

    public function testShouldFilterItemsToNewMapByArrowFunction()
    {
        $newMap = $this->mapEnhanced->filter('($k, $v) => $k >= 1');

        $this->assertNotEquals($this->mapEnhanced, $newMap);
        $this->assertEquals([1 => 'one', 2 => 'two'], $newMap->toArray());
    }

    public function testShouldCombineMapAndFilterToCreateNewMap()
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
    public function testShouldReduceListByArrowFunction($reducer, array $values, $expected)
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
    public function testeShouldReduceListWithInitialValueByArrowFunction(
        $reducer,
        array $values,
        $initialValue,
        $expected
    ) {
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
}