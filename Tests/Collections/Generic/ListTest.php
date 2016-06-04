<?php

namespace MFCollections\Tests\Collections\Generic;

use MFCollections\Collections\CollectionInterface as BaseCollectionInterface;
use MFCollections\Collections\Generic\CollectionInterface;
use MFCollections\Collections\Generic\ListCollection;
use MFCollections\Collections\ListInterface;
use MFCollections\Services\Validators\TypeValidator;

class ListTest extends \PHPUnit_Framework_TestCase
{
    /** @var ListCollection */
    private $list;

    public function setUp()
    {
        $this->list = new ListCollection('string');
    }

    public function testShouldImplementsInterfaces()
    {
        $this->assertInstanceOf(ListInterface::class, $this->list);
        $this->assertInstanceOf(BaseCollectionInterface::class, $this->list);
        $this->assertInstanceOf(CollectionInterface::class, $this->list);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->list);
        $this->assertInstanceOf(\Countable::class, $this->list);
    }

    /**
     * @param string $valueType
     * @param array $values
     *
     * @dataProvider validValuesProvider
     */
    public function testShouldCreateList($valueType, array $values)
    {
        $list = ListCollection::createGenericListFromArray($valueType, $values);

        $this->assertEquals($values, $list->toArray());
    }

    public function validValuesProvider()
    {
        return [
            [
                'type' => TypeValidator::TYPE_STRING,
                'values' => ['value', 'value2'],
            ],
            [
                'type' => TypeValidator::TYPE_INT,
                'values' => [1],
            ],
            [
                'type' => TypeValidator::TYPE_ARRAY,
                'values' => [[2], [3]],
            ],
        ];
    }

    public function testShouldThrowBadMethodUseExceptionWhenCreatingList()
    {
        $this->setExpectedException(\BadMethodCallException::class);

        ListCollection::createFromArray([]);
    }

    public function testShouldThrowBadMethodUseExceptionWhenCreatingGenericCollection()
    {
        $this->setExpectedException(\BadMethodCallException::class);

        ListCollection::createGenericFromArray('string', 'int', []);
    }

    /**
     * @param string $valueType
     * @param array $values
     *
     * @dataProvider invalidValuesProvider
     */
    public function testShouldThrowInvalidArgumentExceptionWhenCreatingBadList($valueType, $values)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        ListCollection::createGenericListFromArray($valueType, $values);
    }

    public function invalidValuesProvider()
    {
        return [
            [
                'type' => TypeValidator::TYPE_INT,
                'values' => ['string'],
            ],
            [
                'type' => TypeValidator::TYPE_STRING,
                'values' => [1.2],
            ],
            [
                'type' => TypeValidator::TYPE_BOOL,
                'values' => [1],
            ],
        ];
    }

    public function testShouldUnshiftValue()
    {
        $firstValue = 'first_value';
        $this->list->add('value');
        $this->list->unshift($firstValue);

        $this->assertCount(2, $this->list);
        $this->assertEquals($firstValue, $this->list->first());
    }

    public function testShouldThrowInvalidArgumentExceptionWhenUnshiftInvalidValue()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->unshift(1);
    }

    public function testShouldCountainsValue()
    {
        $this->assertFalse($this->list->contains('value'));

        $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));
    }

    public function testShouldThrowInvalidArgumentExceptionWhenContainsInvalidType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->contains(true);
    }

    public function testShouldRemoveFirstValue()
    {
        $this->list->add('value');
        $this->list->add('value');

        $this->assertCount(2, $this->list);
        $this->assertTrue($this->list->contains('value'));

        $this->list->removeFirst('value');
        $this->assertCount(1, $this->list);
        $this->assertTrue($this->list->contains('value'));
    }

    public function testShouldThrowInvalidArgumentExceptionOnRemoveFirstValue()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->removeFirst(2);
    }

    public function testShouldRemoveAllValues()
    {
        $this->list->add('value');
        $this->list->add('value');
        $this->list->add('value2');

        $this->assertCount(3, $this->list);
        $this->assertTrue($this->list->contains('value'));
        $this->assertTrue($this->list->contains('value2'));

        $this->list->removeAll('value');
        $this->assertCount(1, $this->list);
        $this->assertFalse($this->list->contains('value'));
        $this->assertTrue($this->list->contains('value2'));
    }

    public function testShouldThrowInvalidArgumentExceptionOnRemoveAllValues()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->removeAll(2.54);
    }

    public function testShouldThrowExceptionWhenForeachItemInListWithArrowFunction()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->each('($k, $v) => {}');
    }

    public function testShouldMapToNewListWithSameGenericType()
    {
        $this->list->add('key');
        $this->list->add('key2');
        $this->list->add('key3');

        $newList = $this->list->map('($v, $i) => $v . "_"');

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key_', 'key2_', 'key3_'], $newList->toArray());
    }

    public function testShouldThrowInvalidArgumentExceptionWhenMapFunctionReturnsBadType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->list->add('key');
        $this->list->add('key2');

        $this->list->map('($v, $i) => 2');
    }

    public function testShouldFilterItemsToNewListByArrowFunction()
    {
        $this->list->add('key');
        $this->list->add('key2');
        $this->list->add('key3');

        $newList = $this->list->filter('($v, $i) => strlen($v) > 3');

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key2', 'key3'], $newList->toArray());
    }

    public function testShouldThrowInvalidArgumentExceptionAfterFilterItemsToNewListByArrowFunction()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $newList = $this->list->filter('($v, $i) => true');

        $newList->add(1);
    }

    public function testShouldCombineMapAndFilterToCreateNewMap()
    {
        $this->list->add('key');
        $this->list->add('key2');

        $newList = $this->list
            ->filter('($v, $i) => $v === "key"')
            ->map('($v, $i) => $v . "_"');

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key_'], $newList->toArray());
    }

    public function testShouldIterateValues()
    {
        $this->list->add('key');
        $this->list->add('key2');

        $array = [];
        foreach ($this->list as $value) {
            $array[] = $value;
        }

        $this->assertEquals(['key', 'key2'], $array);
    }

    public function testShouldReduceGenericList()
    {
        $this->list->add('key');
        $this->list->add('key2');

        $this->assertEquals('key|key2|', $this->list->reduce('($t, $c) => $t . $c . "|"'));
    }

    public function testShouldReduceGenericListOfListCounts()
    {
        $list1 = \MFCollections\Collections\ListCollection::createFromArray([1, 2, 3]);
        $list2 = \MFCollections\Collections\ListCollection::createFromArray(['one', 'two']);

        $list = new ListCollection('instance_of_' . \MFCollections\Collections\ListCollection::class);
        $list->add($list1);
        $list->add($list2);

        $this->assertEquals(5, $list->reduce('($t, $c) => $t + $c->count()'));
    }
}