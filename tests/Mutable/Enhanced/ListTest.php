<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Enhanced;

use MF\Collection\Mutable\IList;

class ListTest extends \MF\Collection\Mutable\ListTest
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
     * @param mixed $expected
     *
     * @dataProvider reduceByArrowFunctionProvider
     */
    public function testShouldReduceListByArrowFunction($reducer, array $values, $expected): void
    {
        $this->listEnhanced = new ListCollection();

        foreach ($values as $value) {
            $this->listEnhanced->add($value);
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
            $this->listEnhanced->add($value);
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

        $immutable = $this->listEnhanced->asImmutable();

        $this->assertInstanceOf(\MF\Collection\Immutable\IList::class, $immutable);
        $this->assertInstanceOf(\MF\Collection\Immutable\Enhanced\ListCollection::class, $immutable);

        $this->assertEquals($this->listEnhanced->toArray(), $immutable->toArray());
    }

    public function testShouldClearCollection(): void
    {
        $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));

        $this->list->clear();
        $this->assertFalse($this->list->contains('value'));
    }

    public function testShouldHasValueByArrowFunction(): void
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->containsBy('($v) => $v === "' . $valueExists . '"'));
        $this->assertFalse($this->list->containsBy('($v) => $v === "' . $valueDoesNotExist . '"'));
    }

    public function testShouldCheckIfCollectionIsEmpty(): void
    {
        $this->list->add('value');
        $this->assertFalse($this->list->isEmpty());

        $this->list->clear();
        $this->assertTrue($this->list->isEmpty());
    }

    public function testShouldMapAndFilterCollectionToNewListCollectionByArrowFunctionWithOneLoopOnly(): void
    {
        $this->listEnhanced = ListCollection::from([1, 2, 3]);

        $newListCollection = $this->listEnhanced
            ->map('($v, $i) => $v + 1')// 2, 3, 4
            ->map('($v, $i) => $v * 2')// 4, 6, 8
            ->filter('($v, $i) => $v % 3 === 0')// 6
            ->map('($v, $i) => $v - 1'); // 5

        $this->assertNotEquals($this->listEnhanced, $newListCollection);
        $this->assertEquals([5], $newListCollection->toArray());
    }

    public function testShouldImplodeItems(): void
    {
        $list = ListCollection::of(1, 2, 3);

        $result = $list->implode(',');

        $this->assertSame('1,2,3', $result);
    }

    public function testShouldGetFirstValueByArrowFunction(): void
    {
        $findSecond = '($value) => $value === "second"';

        $this->assertNull($this->list->firstBy($findSecond));

        $this->list->add('first');
        $this->list->add('second');

        $this->assertSame('second', $this->list->firstBy($findSecond));
    }
}
