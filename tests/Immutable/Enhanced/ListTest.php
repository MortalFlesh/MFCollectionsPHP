<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Enhanced;

use MF\Collection\Immutable\IList;

class ListTest extends \MF\Collection\Immutable\ListTest
{
    /** @var IList|ListCollection */
    private $listEnhanced;

    protected function setUp(): void
    {
        $this->list = new ListCollection();
        $this->listEnhanced = ListCollection::from(['one', 'two', 3]);
    }

    public function testShouldCreateListByCallback(): void
    {
        $list = ListCollection::create(
            explode(',', '1, 2, 3'),
            '($v) => (int) $v'
        );

        $this->assertSame([1, 2, 3], $list->toArray());
    }

    public function testShouldMapCollectionToNewListCollectionByArrowFunction(): void
    {
        $newListCollection = $this->listEnhanced->map('($v, $i) => $i . $v');

        $this->assertNotEquals($this->listEnhanced, $newListCollection);
        $this->assertEquals([0 => '0one', 1 => '1two', 2 => '23'], $newListCollection->toArray());
    }

    public function testShouldFilterItemsToNewListCollectionByArrowFunction(): void
    {
        $newListCollection = $this->listEnhanced->filter('($v) => $v >= 1');

        $this->assertNotEquals($this->listEnhanced, $newListCollection);
        $this->assertEquals([0 => 3], $newListCollection->toArray());
    }

    public function testShouldCombineListCollectionAndFilterToCreateNewListCollection(): void
    {
        $newListCollection = $this->listEnhanced
            ->filter('($v, $i) => $i >= 1')
            ->map('($v, $i) => $i . $v');

        $this->assertNotEquals($this->listEnhanced, $newListCollection);
        $this->assertEquals([0 => '1two', 1 => '23'], $newListCollection->toArray());
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
    ): void {
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

    public function testShouldGetMutableEnhancedListAsImmutableEnhanced(): void
    {
        $this->listEnhanced->add('value');

        $mutable = $this->listEnhanced->asMutable();

        $this->assertInstanceOf(\MF\Collection\IList::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\Enhanced\ListCollection::class, $mutable);

        $this->assertEquals($this->listEnhanced->toArray(), $mutable->toArray());
    }

    public function testShouldHasValueByArrowFunction(): void
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list = $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->containsBy('($v) => $v === "' . $valueExists . '"'));
        $this->assertFalse($this->list->containsBy('($v) => $v === "' . $valueDoesNotExist . '"'));
    }

    public function testShouldClearCollection(): void
    {
        $this->list = $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));

        $this->list = $this->list->clear();
        $this->assertFalse($this->list->contains('value'));
    }

    public function testShouldCheckIfCollectionIsEmpty(): void
    {
        $this->list = $this->list->add('value');
        $this->assertFalse($this->list->isEmpty());

        $this->list = $this->list->clear();
        $this->assertTrue($this->list->isEmpty());
    }

    public function testShouldImplodeItems(): void
    {
        $list = ListCollection::of(1, 2, 3, 4, 5);

        $result = $list
            ->filter('($i) => $i < 4')
            ->implode(',');

        $this->assertSame('1,2,3', $result);
    }
}
