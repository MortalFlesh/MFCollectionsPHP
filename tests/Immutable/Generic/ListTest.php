<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Exception\InvalidArgumentException;
use MF\Collection\Fixtures\ComplexEntity;
use MF\Collection\Fixtures\SimpleEntity;
use MF\Collection\Mutable\Generic\ListCollection as MutableListCollection;

class ListTest extends AbstractTestCase
{
    /** @phpstan-var ListCollection<mixed> */
    private ListCollection $list;

    protected function setUp(): void
    {
        $this->list = new ListCollection();
    }

    /** @dataProvider provideChunkBySize */
    public function testShouldChunkListBySize(int $size, array $data, array $expected): void
    {
        $chunks = ListCollection::from($data)
            ->chunkBySize($size)
            ->toArray();

        $this->assertSame($expected, $chunks);
    }

    public function provideChunkBySize(): array
    {
        return [
            // size, data, expected
            '1' => [1, [1, 2, 3], [[1], [2], [3]]],
            '2' => [2, [1, 2, 3], [[1, 2], [3]]],
            '3' => [3, [1, 2, 3], [[1, 2, 3]]],
        ];
    }

    public function testShouldGroupList(): void
    {
        $groups = ListCollection::from([1, 2, 3, 4, 5, 6, 7])
            ->groupBy(fn (int $i) => $i % 2 === 0 ? 'even' : 'odd')
            ->toArray();

        $expected = [
            new KVPair('odd', ListCollection::of(1, 3, 5, 7)),
            new KVPair('even', ListCollection::of(2, 4, 6)),
        ];

        $this->assertEquals($expected, $groups);
    }

    public function testShouldGroupListAndUseOnlyEvenValues(): void
    {
        $eventValues = ListCollection::from([1, 2, 3, 4, 5, 6, 7])
            ->groupBy(fn (int $i) => $i % 2 === 0 ? 'even' : 'odd')
            ->map(KVPair::value(...))
            ->last()
            ->toArray();

        $expected = [2, 4, 6];

        $this->assertEquals($expected, $eventValues);
    }

    public function testShouldImplementsInterfaces(): void
    {
        $this->assertInstanceOf(IList::class, $this->list);
        $this->assertInstanceOf(IList::class, $this->list);
        $this->assertInstanceOf(ListCollection::class, $this->list);
        $this->assertInstanceOf(ICollection::class, $this->list);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->list);
        $this->assertInstanceOf(\Countable::class, $this->list);

        $this->assertInstanceOf(IList::class, $this->list->add('foo'));
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

    public function testShouldCreateListByCallback(): void
    {
        $list = ListCollection::create(
            explode(',', '1,2,3'),
            fn ($value) => new SimpleEntity((int) $value)
        );

        $list = $list->map(fn ($e) => $e->getId());

        $this->assertSame([1, 2, 3], $list->toArray());
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

    public function testShouldMapToNewListWithSameGenericType(): void
    {
        $this->list = $this->list->add('key');
        $this->list = $this->list->add('key2');
        $this->list = $this->list->add('key3');

        $newList = $this->list->map(fn ($v) => $v . '_');

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key_', 'key2_', 'key3_'], $newList->toArray());
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
            ->map(fn ($v) => $v . '_');

        $this->assertNotEquals($this->list, $newList);
        $this->assertEquals(['key_'], $newList->toArray());
    }

    /** @dataProvider provideValuesToChooseByItsIdentity */
    public function testShouldChooseNotNullValues(array $values, array $expected): void
    {
        $id = fn ($value) => $value;

        $actual = ListCollection::from($values)
            ->choose($id)
            ->toArray();

        $this->assertSame($expected, $actual);
    }

    public static function provideValuesToChooseByItsIdentity(): array
    {
        return [
            // values, expected
            'empty' => [[], []],
            'ints' => [[1, 2, 3], [1, 2, 3]],
            'strings' => [['foo', 'bar'], ['foo', 'bar']],
            'ints with nulls' => [[1, null, 2, 3, null], [1, 2, 3]],
            'strings with nulls' => [[null, 'foo', 'bar', null], ['foo', 'bar']],
        ];
    }

    public function testShouldChooseValuesByCallback(): void
    {
        $evenValues = ListCollection::from([1, 2, 3, 4, 5])
            ->choose(fn (int $i) => $i % 2 === 0 ? $i : null)
            ->toArray();

        $this->assertSame([2, 4], $evenValues);
    }

    public function testShouldNotMapListWithRequiredIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->list = $this->list->add('key');

        $this->list->map(fn ($v, $i) => $v . '_');
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

        $list = new ListCollection();
        $list = $list->add($list1);
        $list = $list->add($list2);

        $this->assertEquals(5, $list->reduce(fn ($t, $c) => $t + $c->count()));
    }

    public function testShouldGetMutableGenericListAsImmutableGenericList(): void
    {
        $this->list = $this->list->add('value');

        $mutable = $this->list->asMutable();

        $this->assertInstanceOf(\MF\Collection\Mutable\Generic\ListCollection::class, $mutable);
        $this->assertEquals($this->list->toArray(), $mutable->toArray());
    }

    public function testShouldMapObjectsToDifferentList(): void
    {
        $list = new ListCollection();
        $list = $list->add(new SimpleEntity(1));
        $list = $list->add(new SimpleEntity(2));
        $list = $list->add(new SimpleEntity(3));

        $sumOfIdsGreaterThan1 = $list
            ->filter(fn ($v, $i) => $v->getId() > 1)
            ->map(fn ($v) => $v->getId())
            ->reduce(fn ($t, $v) => $t + $v);

        $this->assertEquals(5, $sumOfIdsGreaterThan1);
    }

    public function testShouldMapObjectsToDifferentGenericList(): void
    {
        $list = new ListCollection();
        $list = $list->add(new ComplexEntity(new SimpleEntity(1)));
        $list = $list->add(new ComplexEntity(new SimpleEntity(2)));
        $list = $list->add(new ComplexEntity(new SimpleEntity(3)));

        $sumOfIdsGreaterThan1 = $list
            ->filter(fn ($v, $i) => $v->getSimpleEntity()->getId() > 1)
            ->map(fn ($v) => $v->getSimpleEntity())
            ->reduce(fn ($t, $v) => $t + $v->getId());

        $this->assertEquals(5, $sumOfIdsGreaterThan1);
    }

    public function testShouldReduceListWithInitialValue(): void
    {
        $list = new ListCollection();
        $list = $list->add(1);
        $list = $list->add(2);
        $list = $list->add(3);

        $this->assertEquals(10 + 1 + 2 + 3, $list->reduce(fn ($t, $v) => $t + $v, 10));
    }

    public function testShouldReduceListWithInitialValueToOtherType(): void
    {
        $list = new ListCollection();
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

        $callbacks = ListCollection::of(function ($input) {
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

        $sortedList = $list->sort();

        $this->assertNotEquals($list, $sortedList);
        $this->assertEquals([1, 2, 3, 4, 4, 4, 5], $sortedList->toArray());
    }

    public function testShouldSortDescendingValues(): void
    {
        $list = ListCollection::from([1, 4, 3, 4, 2, 5, 4]);
        $sorted = $list->sortDescending();

        $this->assertNotEquals($list, $sorted);
        $this->assertEquals([5, 4, 4, 4, 3, 2, 1], $sorted->toArray());
    }

    public function testShouldReverseValues(): void
    {
        $list = ListCollection::from([1, 4, 3, 4, 2, 5, 4]);
        $sorted = $list->reverse();

        $this->assertNotEquals($list, $sorted);
        $this->assertEquals([4, 5, 2, 4, 3, 4, 1], $sorted->toArray());
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

        $sorted = $list->sortBy(strlen(...));

        $this->assertNotEquals($list, $sorted);
        $this->assertSame(['a', 'bb', 'dd', 'ccc', 'eeeee'], $sorted->toArray());
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

        $sorted = $list->sortBy(strlen(...));
        $this->assertNotEquals($list, $sorted);
        $this->assertSame(['a', 'bb', 'dd', 'ccc', 'eeeee'], $sorted->toArray());

        $sorted = $list
            ->sortByDescending(strlen(...))
            ->reverse();
        $this->assertNotEquals($list, $sorted);
        $this->assertSame(['a', 'dd', 'bb', 'ccc', 'eeeee'], $sorted->toArray());
    }

    public function testShouldAddValueToEndOfList(): void
    {
        $value = 'value';
        $value2 = 'value2';

        $this->list = $this->list->add($value);
        $this->assertEquals($value, $this->list->last());

        $this->list = $this->list->add($value2);
        $this->assertEquals($value2, $this->list->last());
    }

    public function testShouldHasValueBy(): void
    {
        $valueExists = 'has-value';
        $valueDoesNotExist = 'has-no-value';

        $this->list = $this->list->add($valueExists);

        $this->assertContains($valueExists, $this->list);
        $this->assertNotContains($valueDoesNotExist, $this->list);

        $this->assertTrue($this->list->containsBy($this->findByValue($valueExists)));
        $this->assertFalse($this->list->containsBy($this->findByValue($valueDoesNotExist)));
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
        $unique = $list->unique();

        $this->assertNotEquals($list, $unique);
        $this->assertSame([1, 3, 5, 7, 2, 4, 8], $unique->toArray());
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
        $unique = $list->uniqueBy(fn (SimpleEntity $e) => $e->getId());

        $this->assertNotEquals($list, $unique);
        $this->assertEquals(
            [
                new SimpleEntity(1),
                new SimpleEntity(3),
                new SimpleEntity(2),
                new SimpleEntity(4),
                new SimpleEntity(5),
            ],
            $unique->toArray(),
        );
    }

    public function testShouldMapObjectsToDifferentListAndSumValues(): void
    {
        $list = ListCollection::from([
            new SimpleEntity(1),
            new SimpleEntity(2),
            new SimpleEntity(3),
        ]);

        $list = $list
            ->filter(fn (SimpleEntity $v, $i) => $v->getId() > 1)
            ->map(fn (SimpleEntity $v) => $v->getId());
        $sum = $list->sum();

        $this->assertSame(5, $sum);
        $this->assertEquals([2, 3], $list->toArray());
    }

    public function testShouldSumGenericListOfListCountsByCallback(): void
    {
        $list1 = ListCollection::from([1, 2, 3]);
        $list2 = ListCollection::from(['one', 'two']);

        $list = new ListCollection();
        $list = $list
            ->add($list1)
            ->add($list2);

        $this->assertEquals(5, $list->sumBy(count(...)));
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

    public function testShouldAppendLists(): void
    {
        $list1 = ListCollection::of(1, 'string', 2.1);
        $this->assertEquals([1, 'string', 2.1], $list1->toArray());

        $values = [1, 2];
        $values2 = ['three', 'four'];
        $list2 = ListCollection::of(...$values, ...$values2);
        $this->assertEquals([1, 2, 'three', 'four'], $list2->toArray());

        $list3 = $list1->append($list2);
        $this->assertEquals([1, 'string', 2.1, 1, 2, 'three', 'four'], $list3->toArray());
    }

    public function testShouldCollectIntList(): void
    {
        $entity = new class([1, 2, 3]) {
            public function __construct(private readonly array $data)
            {
            }

            public function toArray(): array
            {
                return $this->data;
            }
        };

        $result = ListCollection::of($entity)
            ->collect((fn ($s) => $s->toArray()))
            ->toArray();

        $this->assertSame([1, 2, 3], $result);
    }

    public function testShouldCollectList(): void
    {
        $data = [1, 2, 3];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = ListCollection::from($data)
            ->collect(fn (int $item) => $subData[$item])
            ->reduce(
                fn (string $word, string $subItem) => $word . $subItem,
                'Word: ',
            );

        $this->assertSame('Word: abcdefg', $word);
    }

    public function testShouldMapListCollectAndMapAgain(): void
    {
        $data = ['1 ', ' 2 ', '3'];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = ListCollection::from($data)
            ->map(fn (string $i) => (int) $i)
            ->collect(fn (int $item) => $subData[$item])
            ->filter(fn ($l) => $l < 'f')
            ->map(fn ($l) => $l . ' ')
            ->reduce(
                fn (string $word, string $subItem) => $word . $subItem,
                'Word: ',
            );

        $this->assertSame('Word: a b c d e ', $word);
    }

    public function testShouldConcatIntList(): void
    {
        $result = ListCollection::from([[1, 2, 3], [4, 5, 6], 7, 8])
            ->concat()
            ->toArray();

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8], $result);
    }

    public function testShouldConcatList(): void
    {
        $data = [1, 2, 3];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = ListCollection::from($data)
            ->map(fn (int $item) => $subData[$item])
            ->concat()
            ->reduce(
                fn (string $word, string $subItem) => $word . $subItem,
                'Word: ',
            );

        $this->assertSame('Word: abcdefg', $word);
    }

    public function testShouldMapListConcatAndMapAgain(): void
    {
        $data = ['1 ', ' 2 ', '3'];
        $subData = [
            1 => ['a', 'b', 'c'],
            2 => ['d', 'e'],
            3 => ['f', 'g'],
        ];

        $word = ListCollection::from($data)
            ->map(fn ($i) => (int) $i)
            ->map(fn (int $item) => $subData[$item])
            ->concat()
            ->filter(fn ($l) => $l < 'f')
            ->map(fn ($l) => $l . ' ')
            ->reduce(
                fn (string $word, string $subItem) => $word . $subItem,
                'Word: ',
            );

        $this->assertSame('Word: a b c d e ', $word);
    }

    /** @dataProvider provideSplitData */
    public function testShouldSplitListIntoMultipleLists(int $count, array $data, array $expected): void
    {
        $result = ListCollection::from($data)
            ->splitInto($count)
            ->toArray();

        $this->assertSame($expected, $result);
    }

    public function provideSplitData(): array
    {
        return [
            // count, data, expected
            'count 1' => [1, [1, 2, 3], [[1, 2, 3]]],
            'count 2' => [2, [1, 2, 3], [[1, 2], [3]]],
            'count 2 - even count' => [2, [1, 2, 3, 4], [[1, 2], [3, 4]]],
            'count 3' => [3, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [[1, 2, 3, 4], [5, 6, 7], [8, 9, 10]]],
            'count 4' => [4, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], [[1, 2, 3], [4, 5, 6], [7, 8], [9, 10]]],
            'count 3 in 2 items' => [3, [1, 2], [[1], [2]]],
            'count 5 in 17 items' => [
                5,
                Seq::range('1..17')->toArray(),
                [
                    [1, 2, 3, 4],
                    [5, 6, 7, 8],
                    [9, 10, 11],
                    [12, 13, 14],
                    [15, 16, 17],
                ],
            ],
        ];
    }

    public function testShouldMapListAndUseIndexInMapping(): void
    {
        $list = ListCollection::from([1, 2, 3, 4, 5]);

        $list = $list->mapi(fn ($v, $i) => $i * $v);

        $this->assertSame([0, 2, 6, 12, 20], $list->toArray());
    }
}
