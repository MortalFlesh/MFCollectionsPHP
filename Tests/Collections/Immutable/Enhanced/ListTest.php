<?php

namespace MF\Tests\Collections\Immutable\Enhanced;

use MF\Collections\Immutable\Enhanced\ListCollection;

/**
 * @group unit
 */
class ListTest extends \MF\Tests\Collections\Immutable\ListTest
{
    /** @var ListCollection */
    private $listEnhanced;

    public function setUp()
    {
        $this->list = new ListCollection();
        $this->listEnhanced = ListCollection::createFromArray(['one', 'two', 3]);
    }

    public function testShouldThrowExceptionWhenForeachItemInListCollectionWithArrowFunction()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->listEnhanced->each('($k, $v) => {}');
    }

    public function testShouldMapCollectionToNewListCollectionByArrowFunction()
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

    /**
     * @param callable|string $reducer
     * @param array $values
     * @param mixed $expected
     *
     * @dataProvider reduceByArrowFunctionProvider
     */
    public function testShouldReduceListByArrowFunction($reducer, array $values, $expected)
    {
        $this->listEnhanced = new ListCollection();

        foreach ($values as $value) {
            $this->listEnhanced = $this->listEnhanced->add($value);
        }

        $this->assertEquals($expected, $this->listEnhanced->reduce($reducer));
    }

    public function reduceByArrowFunctionProvider()
    {
        return [
            'total count' => [
                '($total, $current) => $total + $current',
                [1, 2, 3, 4, 5],
                15,
            ],
            'concat strings with indexes' => [
                '($total, $current, $index, $list) => $total . $current . "_" . $index . "|"',
                ['one', 'two', 'three'],
                'one_0|two_1|three_2|',
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
    ) {
        $this->listEnhanced = new ListCollection();

        foreach ($values as $value) {
            $this->listEnhanced = $this->listEnhanced->add($value);
        }

        $this->assertEquals($expected, $this->listEnhanced->reduce($reducer, $initialValue));
    }

    public function reduceInitialByArrowFunctionProvider()
    {
        return [
            'total count' => [
                '($total, $current) => $total + $current',
                [1, 2, 3, 4, 5],
                10,
                25,
            ],
            'total count with empty list' => [
                '($total, $current) => $total + $current',
                [],
                10,
                10,
            ],
            'concat strings with indexes' => [
                '($total, $current, $index, $list) => $total . $current . "_" . $index . "|"',
                ['one', 'two', 'three'],
                'initial-',
                'initial-one_0|two_1|three_2|',
            ],
        ];
    }

    public function testShouldGetMutableEnhancedListAsImmutableEnhanced()
    {
        $this->listEnhanced->add('value');

        $mutable = $this->listEnhanced->asMutable();

        $this->assertInstanceOf(\MF\Collections\ListInterface::class, $mutable);
        $this->assertInstanceOf(\MF\Collections\Enhanced\ListCollection::class, $mutable);

        $this->assertEquals($this->listEnhanced->toArray(), $mutable->toArray());
    }
}
