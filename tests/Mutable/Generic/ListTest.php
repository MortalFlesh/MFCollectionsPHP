<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Fixtures\ComplexEntity;
use MF\Collection\Fixtures\SimpleEntity;
use MF\Collection\Immutable\Generic\KVPair;

class ListTest extends AbstractTestCase
{
    /** @phpstan-var ListCollection<mixed> */
    private ListCollection $list;

    protected function setUp(): void
    {
        $this->list = new ListCollection();
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(IList::class, $this->list);
        $this->assertInstanceOf(ICollection::class, $this->list);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->list);
        $this->assertInstanceOf(\Countable::class, $this->list);
    }

    public function testShouldCreateListOfValues(): void
    {
        $list = ListCollection::of(1, 2, 3);
        $this->assertEquals([1, 2, 3], $list->toArray());

        $values = [1, 2, 3];
        $values2 = [4, 5, 6];
        $list = ListCollection::of(...$values, ...$values2);
        $this->assertEquals([1, 2, 3, 4, 5, 6], $list->toArray());
    }

    public function testShouldCreateListOfMixedValues(): void
    {
        $list = ListCollection::of(1, 'string', 2.1);
        $this->assertEquals([1, 'string', 2.1], $list->toArray());

        $values = [1, 2];
        $values2 = ['three', 'four'];
        $list = ListCollection::of(...$values, ...$values2);
        $this->assertEquals([1, 2, 'three', 'four'], $list->toArray());
    }

    public function testShouldAppendLists(): void
    {
        $list1 = ListCollection::of(1, 'string', 2.1);
        $this->assertEquals([1, 'string', 2.1], $list1->toArray());

        $values = [1, 2];
        $values2 = ['three', 'four'];
        $list2 = ListCollection::of(...$values, ...$values2);
        $this->assertEquals([1, 2, 'three', 'four'], $list2->toArray());

        $list1->append($list2);
        $this->assertEquals([1, 'string', 2.1, 1, 2, 'three', 'four'], $list1->toArray());
    }

    /** @dataProvider validValuesProvider */
    public function testShouldCreateListFromValues(array $values): void
    {
        $list = ListCollection::from($values);

        $this->assertEquals($values, $list->toArray());
    }

    public function validValuesProvider(): array
    {
        return [
            ['values' => ['value', 'value2']],
            ['values' => [1]],
            ['values' => [[2], [3]]],
        ];
    }

    public function testShouldCreateListByCallback(): void
    {
        $list = ListCollection::create(
            explode(',', '1,2,3'),
            fn ($value) => new SimpleEntity((int) $value)
        );

        $list->map(fn (SimpleEntity $e) => $e->getId());

        $this->assertSame([1, 2, 3], $list->toArray());
    }

    public function testShouldUnshiftValue(): void
    {
        $firstValue = 'first_value';
        $this->list->add('value');
        $this->list->unshift($firstValue);

        $this->assertCount(2, $this->list);
        $this->assertEquals($firstValue, $this->list->first());
    }

    public function testShouldGetFirstValue(): void
    {
        $this->assertNull($this->list->first());

        $this->list->add('first');
        $this->list->add('second');

        $this->assertSame('first', $this->list->first());

        foreach ($this->list as $value) {
            $this->assertSame('first', $this->list->first());
        }
    }

    public function testShouldGetFirstValueBy(): void
    {
        $findSecond = fn ($value) => $value === 'second';

        $this->assertNull($this->list->firstBy($findSecond));

        $this->list->add('first');
        $this->list->add('second');

        $this->assertSame('second', $this->list->firstBy($findSecond));
    }

    public function testShouldGetFirstValueByArrowFunction(): void
    {
        $findSecond = fn ($value) => $value === 'second';

        $this->assertNull($this->list->firstBy($findSecond));

        $this->list->add('first');
        $this->list->add('second');

        $this->assertSame('second', $this->list->firstBy($findSecond));
    }

    public function testShouldContainsValue(): void
    {
        $this->assertFalse($this->list->contains('value'));

        $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));
    }

    public function testShouldContainsValueBy(): void
    {
        $this->assertFalse($this->list->contains('value'));

        $this->list->add('value');
        $this->assertTrue($this->list->containsBy(fn ($v) => $v === 'value'));
    }

    public function testShouldNotContainsValueBy(): void
    {
        $this->assertFalse($this->list->contains('value'));

        $this->list->add('value');
        $this->assertFalse($this->list->containsBy(fn ($v) => $v === 'not-there'));
    }

    public function testShouldRemoveFirstValue(): void
    {
        $this->list->add('value');
        $this->list->add('value');

        $this->assertCount(2, $this->list);
        $this->assertTrue($this->list->contains('value'));

        $this->list->removeFirst('value');
        $this->assertCount(1, $this->list);
        $this->assertTrue($this->list->contains('value'));
    }

    public function testShouldRemoveAllValues(): void
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

    public function testShouldMapToNewListWithSameGenericType(): void
    {
        $this->list->add('key');
        $this->list->add('key2');
        $this->list->add('key3');

        $this->list->map(fn ($v) => $v . '_');

        $this->assertEquals(['key_', 'key2_', 'key3_'], $this->list->toArray());
    }

    public function testShouldFilterItemsToNewListByArrowFunction(): void
    {
        $this->list->add('key');
        $this->list->add('key2');
        $this->list->add('key3');

        $this->list->filter(fn ($v, $i) => mb_strlen($v) > 3);

        $this->assertEquals(['key2', 'key3'], $this->list->toArray());
    }

    public function testShouldCombineMapAndFilterToCreateNewMap(): void
    {
        $this->list->add('key');
        $this->list->add('key2');

        $this->list->filter(fn ($v, $i) => $v === 'key');
        $this->list->map(fn ($v) => $v . '_');

        $this->assertEquals(['key_'], $this->list->toArray());
    }

    public function testShouldNotMapListWithRequiredIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->list->add('key');

        $this->list->map(fn ($v, $i) => $v . '_');
    }

    public function testShouldIterateValues(): void
    {
        $this->list->add('key');
        $this->list->add('key2');

        $array = [];
        foreach ($this->list as $value) {
            $array[] = $value;
        }

        $this->assertEquals(['key', 'key2'], $array);
    }

    public function testShouldReduceGenericList(): void
    {
        $this->list->add('key');
        $this->list->add('key2');

        $this->assertEquals('key|key2|', $this->list->reduce(fn ($acc, $v) => $acc . $v . '|'));
    }

    public function testShouldReduceGenericListOfListCounts(): void
    {
        $list1 = ListCollection::from([1, 2, 3]);
        $list2 = ListCollection::from(['one', 'two']);

        $list = new ListCollection();
        $list->add($list1);
        $list->add($list2);

        $this->assertEquals(5, $list->reduce(fn ($t, $c) => $t + $c->count()));
    }

    public function testShouldSumGenericListOfListCountsByCallback(): void
    {
        $list1 = ListCollection::from([1, 2, 3]);
        $list2 = ListCollection::from(['one', 'two']);

        $list = new ListCollection();
        $list->add($list1);
        $list->add($list2);

        $this->assertEquals(5, $list->sumBy(count(...)));
    }

    public function testShouldGetMutableGenericListAsImmutableGenericList(): void
    {
        $this->list->add('value');

        $immutable = $this->list->asImmutable();

        $this->assertInstanceOf(\MF\Collection\Immutable\Generic\IList::class, $immutable);
        $this->assertInstanceOf(\MF\Collection\Immutable\Generic\ListCollection::class, $immutable);

        $this->assertEquals($this->list->toArray(), $immutable->toArray());
    }

    public function testShouldMapObjectsToDifferentListAndSumValues(): void
    {
        $list = new ListCollection();
        $list->add(new SimpleEntity(1));
        $list->add(new SimpleEntity(2));
        $list->add(new SimpleEntity(3));

        $list->filter(fn (SimpleEntity $v, $i) => $v->getId() > 1);
        $list->map(fn (SimpleEntity $v) => $v->getId());
        $sum = $list->sum();

        $this->assertSame(5, $sum);
        $this->assertEquals([2, 3], $list->toArray());
    }

    public function testShouldMapObjectsToDifferentGenericListAndSumValuesByCallback(): void
    {
        $list = new ListCollection();
        $list->add(new ComplexEntity(new SimpleEntity(1)));
        $list->add(new ComplexEntity(new SimpleEntity(2)));
        $list->add(new ComplexEntity(new SimpleEntity(3)));

        $list->filter(fn (ComplexEntity $v, $i) => $v->getSimpleEntity()->getId() > 1);
        $list->map(fn (ComplexEntity $v) => $v->getSimpleEntity());
        $sum = $list->sumBy(fn (SimpleEntity $e) => $e->getId());

        $this->assertEquals(5, $sum);
    }

    public function testShouldReduceListWithInitialValue(): void
    {
        $list = new ListCollection();
        $list->add(1);
        $list->add(2);
        $list->add(3);

        $this->assertEquals(10 + 1 + 2 + 3, $list->reduce(fn ($t, $v) => $t + $v, 10));
    }

    public function testShouldReduceListWithInitialValueToOtherType(): void
    {
        $list = new ListCollection();
        $list->add(1);
        $list->add(2);
        $list->add(3);

        $this->assertEquals('123', $list->reduce(fn ($t, $v) => $t . $v, ''));
    }

    public function testShouldClearCollection(): void
    {
        $this->list->add('value');
        $this->assertTrue($this->list->contains('value'));

        $this->list->clear();
        $this->assertFalse($this->list->contains('value'));
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
        $this->list = ListCollection::from([1, 2, 3]);

        $this->list->map(fn ($v) => $v + 1);            // 2, 3, 4
        $this->list->map(fn ($v) => $v * 2);            // 4, 6, 8
        $this->list->filter(fn ($v, $i) => $v % 3 === 0);   // 6
        $this->list->map(fn ($v) => $v - 1);            // 5
        $this->list->map(fn ($v) => (string) $v);       // '5'

        $this->list->add('6');   // '5', '6'

        $this->assertSame(['5', '6'], $this->list->toArray());
    }

    public function testShouldImplodeItems(): void
    {
        $list = ListCollection::of(1, 2, 3);

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

        $callbacks = ListCollection::of('trim', function ($input) {
            return (int) $input;
        }, $add(1));

        $result = $callbacks->reduce(function ($result, callable $callback) {
            return $callback($result);
        }, '  10');

        $this->assertSame(11, $result);
    }

    public function testShouldSortValues(): void
    {
        $list = ListCollection::from([1, 4, 3, 4, 2, 5, 4]);
        $list->sort();

        $this->assertEquals([1, 2, 3, 4, 4, 4, 5], $list->toArray());
    }

    public function testShouldSortDescendingValues(): void
    {
        $list = ListCollection::from([1, 4, 3, 4, 2, 5, 4]);
        $list->sortDescending();

        $this->assertEquals([5, 4, 4, 4, 3, 2, 1], $list->toArray());
    }

    public function testShouldReverseValues(): void
    {
        $list = ListCollection::from([1, 4, 3, 4, 2, 5, 4]);
        $list->reverse();

        $this->assertEquals([4, 5, 2, 4, 3, 4, 1], $list->toArray());
    }

    public function testShouldSortValuesBy(): void
    {
        $list = ListCollection::from([
            'a',
            'eeeee',
            'bb',
            'ccc',
            'dd',
        ]);

        $list->sortBy(strlen(...));
        $this->assertSame(['a', 'bb', 'dd', 'ccc', 'eeeee'], $list->toArray());
    }

    public function testShouldSortValuesAndReverseThem(): void
    {
        $list = ListCollection::from([
            'a',
            'eeeee',
            'bb',
            'ccc',
            'dd',
        ]);

        $list->sortBy(strlen(...));
        $this->assertSame(['a', 'bb', 'dd', 'ccc', 'eeeee'], $list->toArray());

        $list->sortByDescending(strlen(...));
        $list->reverse();
        $this->assertSame(['a', 'dd', 'bb', 'ccc', 'eeeee'], $list->toArray());
    }

    public function testShouldPopValueFromEnd(): void
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

    public function testShouldShiftValueFromStart(): void
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

    public function testShouldAddValueToEndOfList(): void
    {
        $value = 'value';
        $value2 = 'value2';

        $this->assertNull($this->list->last());

        $this->list->add($value);
        $this->assertEquals($value, $this->list->last());

        $this->list->add($value2);
        $this->assertEquals($value2, $this->list->last());
    }

    public function testShouldForeachItemInList(): void
    {
        $list = ListCollection::from(['one', 'two', 3]);

        $list->each(function ($value, $i): void {
            if ($i === 0) {
                $this->assertEquals('one', $value);
            } elseif ($i === 1) {
                $this->assertEquals('two', $value);
            } elseif ($i === 2) {
                $this->assertEquals(3, $value);
            }
        });
    }

    public function testShouldKeepOnlyUniqueValues(): void
    {
        $list = ListCollection::from([1, 3, 5, 7, 2, 3, 5, 4, 8, 2]);
        $list->unique();

        $this->assertSame([1, 3, 5, 7, 2, 4, 8], $list->toArray());
    }

    public function testShouldKeepOnlyUniqueValuesByCallback(): void
    {
        $list = ListCollection::from([
            new SimpleEntity(1),
            new SimpleEntity(3),
            new SimpleEntity(2),
            new SimpleEntity(4),
            new SimpleEntity(2),
            new SimpleEntity(5),
            new SimpleEntity(1),
        ]);
        $list->uniqueBy(fn (SimpleEntity $e) => $e->getId());

        $this->assertEquals(
            [
                new SimpleEntity(1),
                new SimpleEntity(3),
                new SimpleEntity(2),
                new SimpleEntity(4),
                new SimpleEntity(5),
            ],
            $list->toArray(),
        );
    }

    public function testShouldTransformListToSeq(): void
    {
        $list = ListCollection::from([1, 3, 5, 7, 2, 3, 5, 4, 8, 2]);
        $seq = $list->toSeq();

        $this->assertSame($list->toArray(), $seq->toArray());
    }

    public function testShouldCheckAllValues(): void
    {
        $list = ListCollection::from([1, 3, 5, 7, 2, 3, 5, 4, 8, 2]);

        $this->assertTrue($list->forAll(is_int(...)));
        $this->assertFalse($list->forAll(is_string(...)));
    }

    public function testShouldCountValuesByCallback(): void
    {
        $list = ListCollection::from([1, 3, 5, 7, 2, 3, 5, 4, 8, 2]);

        $counts = $list->countBy(fn ($v) => $v % 2 === 0 ? 'even' : 'odd');

        $this->assertEquals(
            [
                new KVPair('odd', 6),
                new KVPair('even', 4),
            ],
            $counts->toArray(),
        );
    }

    public function testShouldFindMinInList(): void
    {
        $list = ListCollection::from([2, 1, 3, 5]);

        $this->assertSame(1, $list->min());
    }

    public function testShouldFindMaxInList(): void
    {
        $list = ListCollection::from([2, 1, 3, 5]);

        $this->assertSame(5, $list->max());
    }

    public function testShouldFindMinInListByCallback(): void
    {
        $list = ListCollection::from([
            new SimpleEntity(1),
            new SimpleEntity(3),
            new SimpleEntity(2),
        ]);

        $this->assertEquals(new SimpleEntity(1), $list->minBy(fn (SimpleEntity $e) => $e->getId()));
    }

    public function testShouldFindMaxInListByCallback(): void
    {
        $list = ListCollection::from([
            new SimpleEntity(1),
            new SimpleEntity(3),
            new SimpleEntity(2),
        ]);

        $this->assertEquals(new SimpleEntity(3), $list->maxBy(fn (SimpleEntity $e) => $e->getId()));
    }

    public function testShouldMapListAndUseIndexInMapping(): void
    {
        $list = ListCollection::from([1, 2, 3, 4, 5]);

        $list->mapi(fn ($v, $i) => $i * $v);

        $this->assertSame([0, 2, 6, 12, 20], $list->toArray());
    }
}
