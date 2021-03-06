MFCollections for PHP
=====================

[![Latest Stable Version](https://img.shields.io/packagist/v/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![Total Downloads](https://img.shields.io/packagist/dt/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![License](https://img.shields.io/packagist/l/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![Tests and linting](https://github.com/MortalFlesh/MFCollectionsPHP/actions/workflows/tests.yaml/badge.svg)](https://github.com/MortalFlesh/MFCollectionsPHP/actions/workflows/tests.yaml)
[![Coverage Status](https://coveralls.io/repos/github/MortalFlesh/MFCollectionsPHP/badge.svg?branch=master)](https://coveralls.io/github/MortalFlesh/MFCollectionsPHP?branch=master)

It's basically a syntax sugar over classic array structure, which allows you to use it as classic array, but adds some cool features.

## Table of Contents
- [Installation](#installation)
- [Requirements](#requirements)
- [Base Interfaces](#base-interfaces)
    - [IEnumerable](#enumerable-interface)
    - [ICollection](#collection-interface)
    - [IList](#list-interface)
    - [IMap](#map-interface)
    - [ISeq](#seq-interface)
    - [ITuple](#tuple-interface)
- [Mutable](#mutable-collections)
- [Immutable](#immutable-collections)
- [Generic](#generic-collections)
- [Arrow Functions](#arrow-functions)
- [Plans for next versions](#plans-for-next-versions)


## Installation
```bash
composer require mf/collections-php
```


## Requirements
- `PHP ^8.0`


## Base Interfaces

Check out [Documentation](https://github.com/MortalFlesh/MFCollectionsPHP/wiki) for more details.

### <a name="enumerable-interface"></a>IEnumerable
- basic Interface for enumerable
- extends `IteratorAggregate`, `Countable`
- [see Immutable tuple](#immutable-tuple)
- [see Mutable PrioritizedCollection](#mutable-prioritized-collection)

### <a name="collection-interface"></a>ICollection
- basic Interface for Collections
- extends `IEnumerable`
- [see Mutable collections](#mutable-collections)
- [see Immutable collections](#immutable-collections)

### <a name="list-interface"></a>IList
A _list_ is an ordered (_possibly immutable_) series of elements of the same type.
- extends `ICollection`
- [see Mutable list](#mutable-list)
- [see Immutable list](#immutable-list)

### <a name="map-interface"></a>IMap
A _map_ is an ordered (_possibly immutable_) series of key values pairs.
- extends `ICollection, ArrayAccess`
- [see Mutable map](#mutable-map)
- [see Immutable map](#immutable-map)

### <a name="seq-interface"></a>ISeq
A _sequence_ is a logical series of elements all of one type.
- extends `ICollection`
- [see Immutable seq](#immutable-seq)

### <a name="tuple-interface"></a>ITuple
A _tuple_ is a grouping of unnamed but ordered values, possibly of different types.
- extends `IEnumerable`, `ArrayAccess`, `Stringable`
- [see Immutable tuple](#immutable-tuple)


## <a name="mutable-collections"></a>Mutable Collections

### Interfaces
- `Mutable\ICollection`, `Mutable\IList`, `Mutable\IMap`
- extends base version of Interface
- adds methods for mutable Collections only

### <a name="mutable-list"></a>Mutable\ListCollection
- implements `Mutable\IList`
- basic List Collection

### Mutable\Generic\ListCollection
- implements `Generic\IList`
- extends `ListCollection`
- has defined value type and validates it
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods
```php
// list will accept only string values
$list = new Mutable\Generic\ListCollection('string');
```

### <a name="mutable-map"></a>Mutable\Map
- implements `Mutable\IMap`
- basic Map Collection

### Mutable\Generic\Map
- implements `Generic\IMap`
- extends `Map`
- has defined key and value type and validates it
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods
```php
// map will accept only string keys and int values
$map = new Mutable\Generic\Map('string', 'int');
```

### <a name="mutable-prioritized-collection"></a>Mutable\Generic\PrioritizedCollection
- implements `IEnumerable`
- holds items with `generic` type by `priority`

#### Example of strategies by priority
For case when you want to apply `only the first strategy` which can do what you want.
You can add strategies `dynamically` and still apply them `by priority` later. 
```php
// initialization of strategies
$strategies = new PrioritizedCollection(StrategyInterface::class);
$strategies->add(new DefaultStrategy(), 1);

// added later
$strategies->add(new SpecialStrategy(), 100);

// find and apply first suitable strategy
/** @var StrategyInterface $strategy */
foreach ($strategies as $strategy) {
    if ($strategy->supports($somethingStrategic)) {
        return $strategy->apply($somethingStrategic);
    }
}
```


## <a name="immutable-collections"></a>Immutable Collections
- `internal state` of Immutable\Collection instance will `never change` from the outside
```php
$list = new Immutable\ListCollection();
$listWith1 = $list->add(1);

// $list != $listWith1
echo $list->count();        // 0
echo $listWith1->count();   // 1
```
- `$list` is still an empty `Immutable\ListCollection`
- `$listWith1` is new instance of `Immutable\ListCollection` with value `1` 

### Interfaces
- `Immutable\ICollection`, `Immutable\IList`, `Immutable\IMap`, `Immutable\ISeq`
- extends base version of Interface
- adds methods for immutable Collections only or alters method return value

### <a name="immutable-list"></a>Immutable\ListCollection
- implements `Immutable\IList`
- basic Immutable List Collection

### Immutable\Generic\ListCollection
- implements `Generic\IList`
- extends `Immutable\ListCollection`
- has defined value type and validates it
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods
```php
// list will accept only string values
$list = new Immutable\Generic\ListCollection('string');
```

### <a name="immutable-map"></a>Immutable\Map
- implements `Immutable\IMap`
- basic Immutable Map Collection

### Immutable\Generic\Map
- implements `Generic\IMap`
- extends `Immutable\ListCollection`
- has defined value type and validates it
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods
```php
// map will accept only string values and int keys
$map = new Immutable\Generic\Map('int', 'string');
```

### <a name="immutable-seq"></a>Immutable\Seq
- implements `Immutable\ISeq`
- basic Immutable Sequence
- is `lazy` as possible (_even could be `Infinite`_)
- allows `Arrow Functions` everywhere where `callable` is wanted
```php
Seq::infinite()
    ->filter(fn($i) => $i % 2 === 0)
    ->map(fn($i) => $i * $i)
    ->takeWhile(fn($i) => $i < 25)
    ->toArray();
// [4, 16]
```

### <a name="immutable-tuple"></a>Immutable\Tuple
- implements `Immutable\ITuple`
- basic Immutable Tuple
- must have at least 2 values (_otherwise it is just a single value_)
- is `eager` as possible
- allows `destructuring`, `matching` and `parsing`/`formatting`
- can contain any scalar values and/or arrays
    - in string representation of a `Tuple`, array values must be separated by `;` (_not by `,`_)

#### Parsing
```php
Tuple::parse('(foo, bar)')->toArray();                  // ['foo', 'bar']
Tuple::parse('("foo, bar", boo)')->toArray();           // ['foo, bar', 'boo']
Tuple::parse('(1, "foo, bar", true)')->toArray();       // [1, 'foo, bar', true]
Tuple::parse('(1, [2; 3], [four; "Five"])')->toArray(); // [1, [2, 3], ['four', 'five']]
```

#### Matching and comparing
```php
Tuple::from([1, 1])->match('int', 'int');                      // true
Tuple::from([1, 2, 3])->isSame(Tuple::of(1, 2, 3));            // true
Tuple::of(10, 'Foo', null)->match('int', 'string', '?string'); // true
Tuple::of(10, [9, 8])->match('int', 'array');                  // true
```

#### Parsing and matching
```php
Tuple::parseMatch('(foo, bar)', 'string', 'string')->toArray();        // ['foo', 'bar']
Tuple::parseMatchTypes('(foo, bar)', ['string', 'string'])->toArray(); // ['foo', 'bar']

// invalid types
Tuple::parseMatch('(foo, bar, 1)', 'string', 'string'); // throws \InvalidArgumentException "Given tuple does NOT match expected types (string, string) - got (string, string, int)"
```

#### Formatting
```php
Tuple::from([1, 'foo', null])->toString();          // '(1, "foo", null)'

// for URL
Tuple::from(['foo', 'bar'])->toStringForUrl();      // '(foo,bar)'
Tuple::from(['foo-bar', 'boo'])->toStringForUrl();  // '(foo-bar,bar)'
Tuple::from(['mail', 'a@b.com'])->toStringForUrl(); // '(mail,"a@b.com")'
```

#### Destructuring
```php
$tuple  = Tuple::of('first', 2, 3); // ('first', 2, 3)
$first  = $tuple->first();          // 'first'
$second = $tuple->second();         // 2
[$first, $second] = $tuple;         // $first = 'first'; $second = 2
[,, $third]       = $tuple;         // 3
```

#### Unpacking
```php
sprintf('Title: %s | Value: %s', ...Tuple::of('foo', 'bar')); // "Title: foo | Value: bar"
```

#### Merging
- merging `Tuples` will automatically flat them (_see last example below_)
```php
$base  = Tuple::of('one', 'two');                       // ('one', 'two')
$upTo3 = Tuple::merge($base, 'three');                  // ('one', 'two', 'three')
$upTo4 = Tuple::merge($base, '3', 'four');              // ('one', 'two', '3', 'four')
$upTo5 = Tuple::merge($base, ['3', '4'], '5');          // ('one', 'two', ['3', '4'], '5')
$upTo5 = Tuple::merge($base, Tuple::of('3', '4'), '5'); // ('one', 'two', '3', '4', '5')
```

#### Merging and matching
```php
$base = Tuple::of('one', 'two');                                    // ('one', 'two')
$upTo3 = Tuple::mergeMatch(['string', 'string', 'int'], $base, 3);  // ('one', 'two', 3)

// invalid types
Tuple::mergeMatch(['string', 'string'], $base, 3); // throws \InvalidArgumentException "Merged tuple does NOT match expected types (string, string) - got (string, string, int)."
```


## Generic Collections
- It's basically strictly validated Collection
- To see more check: [MF/TypeValidator](https://github.com/MortalFlesh/TypeValidator)

### Generic Interfaces
- `Generic\ICollection`, `Generic\IList`, `Generic\IMap`
- extends base version of Interface
- adds generic functionality to Collections, which will validate types
- each Generic Collection implements its Generic Interface


## Arrow Functions

### Usage:
```php
$map = new Mutable\Map();
$map->set(1, 'one');
$map[2] = 'two';

$map->toArray(); // [1 => 'one', 2 => 'two']

$map
    ->filter(fn($v, $k) => $k > 1)
    ->map(fn($v, $k) => $k . " - " . $v)
    ->toArray(); // [2 => '2 - two']

//against classic PHP

$array = [1 => 'one', 2 => 'two'];

array_map(
    function ($k, $v) {
        return $k . ' - ' . $v;
    }, 
    array_filter(
        function ($k, $v) {
            return $k > 1;
        },
        $array
    )
);
```

### With generics:
```php
class SimpleEntity
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

$list = new Mutable\Generic\ListCollection(SimpleEntity::class);
$list->add(new SimpleEntity(1));
$list->add(new SimpleEntity(2));
$list->add(new SimpleEntity(3));

$sumOfIdsGreaterThan1 = $list
    ->filter(fn($v, $i) => $v->getId() > 1) // filter entities with id > 1
    ->map(fn($v, $i) => $v->getId())        // map filtered entities to just ids
    ->reduce(fn($t, $v) => $t + $v);        // reduce ids to their sum

echo $sumOfIdsGreaterThan1;     // 5
```


### Some performance tests:
- benchmarks and memory usage tests ([here](https://github.com/MortalFlesh/PerformanceTests))

## Lazy mapping
- if your `Collection` get mapped and filtered many times (_for readability_), it is not a problem
    - `map -> map -> filter -> map -> filter -> map` will iterate the collection **only once** (_for applying all modifiers at once_)
    - this modification is done when another method is triggered, so adding new modifier is an **atomic** operation


## Plans for next versions
- in `ListCollection` and `Map` supports `iterable` instead of just `array` to allow `Seq`
- add `Generic/Seq`
- use `Symfony/Stopwatch` in unit tests
- **IMap** change order of `key/value` in `map` and `filter` to `value/key` (**BC**)
- _even better_ documentation ([current](https://github.com/MortalFlesh/MFCollectionsPHP/wiki))
- **methods**:
    - ICollection::forAll(callback):bool
    - IMap::firstKey()
    - IMap::firstValue() 
    - IMap::first(callback|null): Tuple|null
    - IList::first(callback|null): TValue|null
    - ICollection::create(iterable<TKey, mixed> $source, (mixed value, TKey index) => TValue): string
