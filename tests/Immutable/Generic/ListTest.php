<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\BadMethodCallException;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Fixtures\ComplexEntity;
use MF\Collection\Fixtures\EntityInterface;
use MF\Collection\Fixtures\SimpleEntity;
use MF\Collection\Generic\ICollection;
use MF\Collection\Generic\IList as GenericListInterface;
use MF\Collection\ICollection as BaseCollectionInterface;
use MF\Collection\IList as BaseListInterface;
use MF\Collection\Immutable\Generic\IList as ImmutableGenericInterface;
use MF\Collection\Immutable\IList;
use MF\Collection\Immutable\ListCollection as BaseImmutableListCollection;
use MF\Collection\Mutable\ListCollection as MutableListCollection;
use MF\Validator\TypeValidator;

class ListTest extends AbstractTestCase
{
    /** @var ListCollection|ImmutableGenericInterface */
    private $list;

    protected function setUp(): void
    {
        $this->list = new ListCollection('string');
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(ImmutableGenericInterface::class, $this->list);
        $this->assertInstanceOf(GenericListInterface::class, $this->list);
        $this->assertInstanceOf(IList::class, $this->list);
        $this->assertInstanceOf(BaseListInterface::class, $this->list);
        $this->assertInstanceOf(BaseImmutableListCollection::class, $this->list);
        $this->assertInstanceOf(ICollection::class, $this->list);
        $this->assertInstanceOf(BaseCollectionInterface::class, $this->list);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->list);
        $this->assertInstanceOf(\Countable::class, $this->list);

        $this->assertInstanceOf(ImmutableGenericInterface::class, $this->list->add('foo'));
    }

    public function testShouldCreateListOfValues(): void
    {
        $list = ListCollection::ofT('int', 1, 2, 3);
        $this->assertEquals([1, 2, 3], $list->toArray());

        $values = [1, 2, 3];
        $values2 = [4, 5, 6];
        $list = ListCollection::ofT('int', ...$values, ...$values2);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $list->toArray());
    }

    public function testShouldCreateListOfMixedValues(): void
    {
        $list = ListCollection::ofT('mixed', 1, 'string', 2.1);
        $this->assertEquals([1, 'string', 2.1], $list->toArray());

        $values = [1, 2];
        $values2 = ['three', 'four'];
        $list = ListCollection::ofT('any', ...$values, ...$values2);
        $this->assertEquals([1, 2, 'three', 'four'], $list->toArray());
    }

    public function testShouldNotCreateListOfDifferentValueTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ListCollection::ofT('int', 1, 'string', 3);
    }

    /** @dataProvider validValuesProvider */
    public function testShouldCreateList(string $valueType, array $values): void
    {
        $list = ListCollection::fromT($valueType, $values);

        $this->assertEquals($values, $list->toArray());
    }

    public function validValuesProvider(): array
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

    public function testShouldThrowBadMethodUseExceptionWhenCreatingListOfValues(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This method should not be used with Immutable Generic List. Use ofT instead.');

        ListCollection::of(1);
    }

    public function testShouldThrowBadMethodUseExceptionWhenCreatingList(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This method should not be used with Immutable Generic List. Use fromT instead.');

        ListCollection::from([]);
    }

    /** @dataProvider invalidValuesProvider */
    public function testShouldThrowInvalidArgumentExceptionWhenCreatingBadList(string $valueType, array $values): void
    {
        $this->expectException(InvalidArgumentException::class);

        ListCollection::fromT($valueType, $values);
    }

    public function invalidValuesProvider(): array
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

    public function testShouldCreateListByCallback(): void
    {
        $list = ListCollection::createT(
            SimpleEntity::class,
            explode(',', '1,2,3'),
            function ($value) {
                return new SimpleEntity((int) $value);
            }
        );

        $list = $list->map(fn ($e) => $e->getId(), 'int');

        $this->assertSame([1, 2, 3], $list->toArray());
    }

    public function testShouldThrowBadMethodUseExceptionWhenCreatingListByCallback(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('This method should not be used with Generic List. Use createT instead.');

        ListCollection::create([], function ($v) {
            return $v;
        });
    }

    public function testShouldUnshiftValue(): void
    {
        $firstValue = 'first_value';
        $this->list = $this->list->add('value');
        $this->list = $this->list->unshift($firstValue);
        $this->list->add('irelevant-value');

        $this->assertCount(2, $this->list);
        $this->assertEquals($firstValue, $this->list->first());
    }

    public function testShouldGetFirstValue(): void
    {
        $this->assertNull($this->list->first());

        $this->list = $this->list->add('first');
        $this->list = $this->list->add('second');

        $this->assertSame('first', $this->list->first());

        foreach ($this->list as $value) {
            $this->assertSame('first', $this->list->first());
        }
    }

    public function testShouldGetFirstValueBy(): void
    {
        $findSecond = function ($value) {
            return $value === 'second';
        };

        $this->assertNull($this->list->firstBy($findSecond));

        $this->list = $this->list->add('first');
        $this->list = $this->list->add('second');

        $this->assertSame('second', $this->list->firstBy($findSecond));
    }

    public function testShouldGetFirstValueByArrowFunction(): void
    {
        $findSecond = fn ($value) => $value === 'second';

        $this->assertNull($this->list->firstBy($findSecond));

        $this->list = $this->list->add('first');
        $this->list = $this->list->add('second');

        $this->assertSame('second', $this->list->firstBy($findSecond));
    }

    public function testShouldContainsValue(): void
    {
        $this->assertFalse($this->list->contains('value'));

        $this->list = $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));
    }

    public function testShouldContainsValueBy(): void
    {
        $this->assertFalse($this->list->contains('value'));

        $this->list = $this->list->add('value');
        $this->assertTrue($this->list->containsBy(fn ($v) => $v === 'value'));
    }

    public function testShouldRemoveFirstValue(): void
    {
        $this->list = $this->list->add('value');
        $this->list = $this->list->add('value');

        $this->assertCount(2, $this->list);
        $this->assertTrue($this->list->contains('value'));

        $this->list = $this->list->removeFirst('value');
        $this->assertCount(1, $this->list);
        $this->assertTrue($this->list->contains('value'));
    }

    public function testShouldNotRemoveFirstValue(): void
    {
        $this->list = $this->list->add('value');

        $this->assertCount(1, $this->list);
        $this->assertTrue($this->list->contains('value'));
        $this->assertFalse($this->list->contains('not-existed-value'));

        $this->list = $this->list->removeFirst('not-existed-value');

        $this->assertCount(1, $this->list);
        $this->assertTrue($this->list->contains('value'));
        $this->assertFalse($this->list->contains('not-existed-value'));
    }

    public function testShouldThrowInvalidArgumentExceptionOnRemoveFirstValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->list->removeFirst(2);
    }

    public function testShouldRemoveAllValues(): void
    {
        $this->list = $this->list->add('value');
        $this->list = $this->list->add('value');
        $this->list = $this->list->add('value2');

        $this->assertCount(3, $this->list);
        $this->assertTrue($this->list->contains('value'));
        $this->assertTrue($this->list->contains('value2'));

        $this->list = $this->list->removeAll('value');
        $this->assertCount(1, $this->list);
        $this->assertFalse($this->list->contains('value'));
        $this->assertTrue($this->list->contains('value2'));
    }

    public function testShouldThrowInvalidArgumentExceptionOnRemoveAllValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->list->removeAll(2.54);
    }

    public function testShouldMapToNewListWithSameGenericType(): void
    {
        $this->list = $this->list->add('key');
        $this->list = $this->list->add('key2');
        $this->list = $this->list->add('key3');

        $newList = $this->list->map(fn ($v, $i) => $v . '_');

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key_', 'key2_', 'key3_'], $newList->toArray());
    }

    public function testShouldThrowInvalidArgumentExceptionWhenMapFunctionReturnsBadType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->list = $this->list->add('key');
        $this->list = $this->list->add('key2');

        $this->list
            ->map(fn ($v, $i) => 2, 'string')
            ->toArray();
    }

    public function testShouldFilterItemsToNewListByArrowFunction(): void
    {
        $this->list = $this->list->add('key');
        $this->list = $this->list->add('key2');
        $this->list = $this->list->add('key3');

        $newList = $this->list->filter(fn ($v, $i) => mb_strlen($v) > 3);

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key2', 'key3'], $newList->toArray());
    }

    public function testShouldCombineMapAndFilterToCreateNewMap(): void
    {
        $this->list = $this->list->add('key');
        $this->list = $this->list->add('key2');

        $newList = $this->list
            ->filter(fn ($v, $i) => $v === 'key')
            ->map(fn ($v, $i) => $v . '_');

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key_'], $newList->toArray());
    }

    public function testShouldIterateValues(): void
    {
        $this->list = $this->list->add('key');
        $this->list = $this->list->add('key2');

        $array = [];
        foreach ($this->list as $value) {
            $array[] = $value;
        }

        $this->assertEquals(['key', 'key2'], $array);
    }

    public function testShouldReduceGenericList(): void
    {
        $this->list = $this->list->add('key');
        $this->list = $this->list->add('key2');

        $this->assertEquals('key|key2|', $this->list->reduce(fn ($t, $c) => $t . $c . '|'));
    }

    public function testShouldReduceGenericListOfListCounts(): void
    {
        $list1 = MutableListCollection::from([1, 2, 3]);
        $list2 = MutableListCollection::from(['one', 'two']);

        $list = new ListCollection(MutableListCollection::class);
        $list = $list->add($list1);
        $list = $list->add($list2);

        $this->assertEquals(5, $list->reduce(fn ($t, $c) => $t + $c->count()));
    }

    public function testShouldGetMutableGenericListAsImmutableGenericList(): void
    {
        $this->list = $this->list->add('value');

        $mutable = $this->list->asMutable();

        $this->assertInstanceOf(\MF\Collection\IList::class, $mutable);
        $this->assertInstanceOf(\MF\Collection\Mutable\Generic\ListCollection::class, $mutable);

        $this->assertEquals($this->list->toArray(), $mutable->toArray());
    }

    public function testShouldMapObjectsToDifferentList(): void
    {
        $list = new ListCollection(SimpleEntity::class);
        $list = $list->add(new SimpleEntity(1));
        $list = $list->add(new SimpleEntity(2));
        $list = $list->add(new SimpleEntity(3));

        $sumOfIdsGreaterThan1 = $list
            ->filter(fn ($v, $i) => $v->getId() > 1)
            ->map(fn ($v, $i) => $v->getId(), 'int')
            ->reduce(fn ($t, $v) => $t + $v);

        $this->assertEquals(5, $sumOfIdsGreaterThan1);
    }

    public function testShouldMapObjectsToDifferentGenericList(): void
    {
        $list = new ListCollection(EntityInterface::class);
        $list = $list->add(new ComplexEntity(new SimpleEntity(1)));
        $list = $list->add(new ComplexEntity(new SimpleEntity(2)));
        $list = $list->add(new ComplexEntity(new SimpleEntity(3)));

        $sumOfIdsGreaterThan1 = $list
            ->filter(fn ($v, $i) => $v->getSimpleEntity()->getId() > 1)
            ->map(fn ($v, $i) => $v->getSimpleEntity(), SimpleEntity::class)
            ->reduce(fn ($t, $v) => $t + $v->getId());

        $this->assertEquals(5, $sumOfIdsGreaterThan1);
    }

    public function testShouldReduceListWithInitialValue(): void
    {
        $list = new ListCollection('int');
        $list = $list->add(1);
        $list = $list->add(2);
        $list = $list->add(3);

        $this->assertEquals(10 + 1 + 2 + 3, $list->reduce(fn ($t, $v) => $t + $v, 10));
    }

    public function testShouldReduceListWithInitialValueToOtherType(): void
    {
        $list = new ListCollection('int');
        $list = $list->add(1);
        $list = $list->add(2);
        $list = $list->add(3);

        $this->assertEquals('123', $list->reduce(fn ($t, $v) => $t . $v, ''));
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
        $list = ListCollection::ofT('int', 1, 2, 3);

        $result = $list->implode(',');

        $this->assertSame('1,2,3', $result);
    }

    public function testShouldReduceAllGivenCallbacks(): void
    {
        $add = function ($a) {
            return function ($b) use ($a) {
                return $a + $b;
            };
        };

        $callbacks = ListCollection::ofT('callable', 'trim', function ($input) {
            return (int) $input;
        }, $add(1));

        $result = $callbacks->reduce(function ($result, callable $callback) {
            return $callback($result);
        }, '  10');

        $this->assertSame(11, $result);
    }

    public function testShouldSortValues(): void
    {
        $list = ListCollection::fromT('int', [1, 4, 3, 4, 2, 5, 4]);

        $sortedList = $list->sort();

        $this->assertNotEquals($list, $sortedList);
        $this->assertEquals([1, 2, 3, 4, 4, 4, 5], $sortedList->toArray());
    }
}
