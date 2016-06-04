<?php

namespace MFCollections\Tests\Collections;

use MFCollections\Collections\CollectionInterface;
use MFCollections\Collections\ListCollection;
use MFCollections\Collections\ListInterface;

class ListTest extends \PHPUnit_Framework_TestCase
{
    /** @var ListCollection */
    protected $list;

    public function setUp()
    {
        $this->list = new ListCollection();
    }

    public function testShouldImplementsInterfaces()
    {
        $this->assertInstanceOf(ListInterface::class, $this->list);
        $this->assertInstanceOf(CollectionInterface::class, $this->list);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->list);
        $this->assertInstanceOf(\Countable::class, $this->list);
    }

    /**
     * @param array $array
     * @param bool $recursive
     *
     * @dataProvider arrayProvider
     */
    public function testShouldCreateMapFromArray(array $array, $recursive)
    {
        $list = ListCollection::createFromArray($array, $recursive);

        $this->assertEquals($array, $list->toArray());
    }

    public function arrayProvider()
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
                'array' => [1, 'value', 3, ['val', 4], [[5, 6]]],
                'recursive' => true,
            ],
            [
                'array' => [1, 'value', 3, ['val', 4], [[5, 6]]],
                'recursive' => false,
            ],
        ];
    }

    /**
     * @param bool $recursive
     *
     * @dataProvider recursiveProvider
     */
    public function testShouldCreateMapFromArrayWithSubArray($recursive)
    {
        $subArray = ['value'];

        $array = [
            1,
            $subArray,
        ];

        $list = ListCollection::createFromArray($array, $recursive);

        if ($recursive) {
            $this->assertInstanceOf(ListCollection::class, $list->last());
        } else {
            $this->assertEquals($subArray, $list->last());
        }
    }

    public function recursiveProvider()
    {
        return [
            ['recursive' => false],
            ['recursive' => true],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider addItemsProvider
     */
    public function testShouldAddItemsToMap($value)
    {
        $this->list->add($value);

        $this->assertEquals($value, $this->list->pop());
    }

    public function addItemsProvider()
    {
        return [
            ['value' => 'string-value'],
            ['value' => 2],
            ['value' => 42],
            ['value' => false],
            ['value' => 24.12],
        ];
    }

    public function testShouldIterateThroughMap()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $i = 0;
        foreach ($list as $value) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals(3, $value);
            }
            $i++;
        }
    }

    /**
     * @param array $array
     *
     * @dataProvider arrayProvider
     */
    public function testShouldGetCount(array $array)
    {
        $originalCount = count($array);
        $list = ListCollection::createFromArray($array);

        $this->assertCount($originalCount, $list);

        $list->add('value');
        $this->assertCount($originalCount + 1, $list);
    }

    public function testShouldHasValue()
    {
        $valueExists = 'has-value';
        $valueDoesntExist = 'has-no-value';

        $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesntExist, $this->list);

        $this->assertTrue($this->list->contains($valueExists));
        $this->assertFalse($this->list->contains($valueDoesntExist));
    }

    public function testShouldRemoveFirst()
    {
        $value = 'value';

        $this->list->add($value);
        $this->list->add($value);

        $this->assertCount(2, $this->list);
        $this->assertEquals(2, $this->list->count());
        $this->assertTrue($this->list->contains($value));

        $this->list->removeFirst($value);

        $this->assertCount(1, $this->list);
        $this->assertEquals(1, $this->list->count());
        $this->assertTrue($this->list->contains($value));

        $this->assertEquals($value, $this->list->first());
    }

    public function testShouldRemoveAll()
    {
        $value = 'value';
        $value2 = 'value2';

        $this->list->add($value);
        $this->list->add($value2);
        $this->list->add($value);

        $this->assertCount(3, $this->list);
        $this->assertEquals(3, $this->list->count());
        $this->assertTrue($this->list->contains($value));
        $this->assertTrue($this->list->contains($value2));

        $this->list->removeAll($value);

        $this->assertCount(1, $this->list);
        $this->assertEquals(1, $this->list->count());
        $this->assertFalse($this->list->contains($value));
        $this->assertTrue($this->list->contains($value2));
    }

    public function testShouldAddValueToEndOfList()
    {
        $value = 'value';
        $value2 = 'value2';

        $this->list->add($value);
        $this->assertEquals($value, $this->list->last());

        $this->list->add($value2);
        $this->assertEquals($value2, $this->list->last());
    }

    public function testShouldUnshiftValue()
    {
        $value = 'value';
        $value2 = 'value2';
        $valueToUnshift = 'valueToUnshift';

        $this->list->add($value);
        $this->assertEquals($value, $this->list->first());

        $this->list->add($value2);
        $this->assertEquals($value, $this->list->first());

        $this->list->unshift($valueToUnshift);
        $this->assertEquals($valueToUnshift, $this->list->first());
    }

    public function testShouldShiftValueFromStart()
    {
        $firstValue = 'value';
        $value2 = 'value2';

        $this->list->add($firstValue);
        $this->list->add($value2);
        $this->assertCount(2, $this->list);

        $result = $this->list->shift();
        $this->assertCount(1, $this->list);
        $this->assertEquals($firstValue, $result);
    }

    public function testShouldPopValueFromEnd()
    {
        $value = 'value';
        $lastValue = 'value2';

        $this->list->add($value);
        $this->list->add($lastValue);
        $this->assertCount(2, $this->list);

        $result = $this->list->pop();
        $this->assertCount(1, $this->list);
        $this->assertEquals($lastValue, $result);
    }

    public function testShouldSortValues()
    {
        $list = ListCollection::createFromArray([1, 4, 3, 4, 2, 5, 4]);

        $sortedList = $list->sort();

        $this->assertNotEquals($list, $sortedList);
        $this->assertEquals([1, 2, 3, 4, 4, 4, 5], $sortedList->toArray());
    }

    public function testShouldForeachItemInMap()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $list->each(function ($value, $i) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 1) {
                $this->assertEquals(3, $value);
            }
        });
    }

    public function testShouldMapItemsToNewMap()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $newList = $list->map(function ($value, $i) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals(3, $value);
            }

            return $i . $value;
        });

        $this->assertNotEquals($list, $newList);
        $this->assertEquals([0 => '0one', 1 => '1two', 2 => '23'], $newList->toArray());
    }

    public function testShouldFilterMapToNewMap()
    {
        $list = ListCollection::createFromArray(['one', 'two', 3]);

        $newList = $list->filter(function ($value, $i) {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals(3, $value);
            }

            return is_string($value);
        });

        $this->assertEquals([0 => 'one', 1 => 'two'], $newList->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToEach()
    {
        $this->list->each(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToMap()
    {
        $this->list->map(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnSettingNotCallableCallbackToFilter()
    {
        $this->list->filter(1);
    }
}
