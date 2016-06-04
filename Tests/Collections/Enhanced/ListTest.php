<?php

namespace MFCollections\Tests\Collections\Enhanced;

use MFCollections\Collections\Enhanced\ListCollection;

class ListTest extends \MFCollections\Tests\Collections\ListTest
{
    /** @var ListCollection */
    private $listEnhanced;

    public function setUp()
    {
        $this->list = new ListCollection();
        $this->listEnhanced = ListCollection::createFromArray(['one', 'two', 3]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowExceptionWhenForeachItemInListCollectionWithArrowFunction()
    {
        $this->listEnhanced->each('($k, $v) => {}');
    }

    public function testShouldListCollectionToNewListCollectionByArrowFunction()
    {
        $newListCollection = $this->listEnhanced->map('($v, $i) => $i . $v');

        $this->assertNotEquals($this->listEnhanced, $newListCollection);
        $this->assertEquals([0 => '0one', 1 => '1two', 2 => '23'], $newListCollection->toArray());
    }

    public function testShouldFilterItemsToNewListCollectionByArrowFunction()
    {
        $newListCollection = $this->listEnhanced->filter('($v) => $v >= 1');

        $this->assertNotEquals($this->listEnhanced, $newListCollection);
        $this->assertEquals([0 => 3], $newListCollection->toArray());
    }

    public function testShouldCombineListCollectionAndFilterToCreateNewListCollection()
    {
        $newListCollection = $this->listEnhanced
            ->filter('($v, $i) => $i >= 1')
            ->map('($v, $i) => $i . $v');

        $this->assertNotEquals($this->listEnhanced, $newListCollection);
        $this->assertEquals([0 => '0two', 1 => '13'], $newListCollection->toArray());
    }
}
