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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowExceptionWhenForeachItemInMapWithArrowFunction()
    {
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
}
