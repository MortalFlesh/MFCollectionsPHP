MFCollections for PHP
=====================

[![Latest Stable Version](https://img.shields.io/packagist/v/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![Total Downloads](https://img.shields.io/packagist/dt/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![License](https://img.shields.io/packagist/l/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![Build Status](https://travis-ci.org/MortalFlesh/MFCollectionsPHP.svg?branch=master)](https://travis-ci.org/MortalFlesh/MFCollectionsPHP)
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
- `PHP >=7.1`
- `eval()` function for parsing [Arrow Functions](#arrow-functions)


## Base Interfaces

Check out [Documentation](https://github.com/MortalFlesh/MFCollectionsPHP/wiki) for more details.

### <a name="enumerable-interface"></a>IEnumerable
- basic Interface for enumerable
- extends `IteratorAggregate`, `Countable`

### <a name="collection-interface"></a>ICollection
- basic Interface for Collections
- extends `IEnumerable`
- [see Mutable collections](#mutable-collections)
- [see Immutable collections](#immutable-collections)

### <a name="list-interface"></a>IList
- extends `ICollection`
- [see Mutable list](#mutable-list)
- [see Immutable list](#immutable-list)

### <a name="map-interface"></a>IMap
- extends `ICollection, ArrayAccess`
- [see Mutable map](#mutable-map)
- [see Immutable map](#immutable-map)

### <a name="seq-interface"></a>ISeq
- extends `ICollection`
- [see Immutable seq](#immutable-seq)

### <a name="tuple-interface"></a>ITuple
- extends `IEnumerable`, `ArrayAccess`
- [see Immutable tuple](#immutable-tuple)


## <a name="mutable-collections"></a>Mutable Collections

### Interfaces
- `Mutable\ICollection`, `Mutable\IList`, `Mutable\IMap`
- extends base version of Interface
- adds methods for mutable Collections only

### <a name="mutable-list"></a>Mutable\ListCollection
- implements `Mutable\IList`
- basic List Collection

### Mutable\Enhanced\ListCollection
- extends `ListCollection`
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods

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

### Mutable\Enhanced\Map
- extends `Map`
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods

### Mutable\Generic\Map
- implements `Generic\IMap`
- extends `Map`
- has defined key and value type and validates it
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods
```php
// map will accept only string keys and int values
$map = new Mutable\Generic\Map('string', 'int');
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

### Immutable\Enhanced\ListCollection
- extends `Immutable\ListCollection`
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods

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

### Immutable\Enhanced\Map
- extends `Immutable\Map`
- adds possibility of usage `Arrow Functions` in `map()`, `filter()` and `reduce()` methods

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
    ->filter('($i) => $i % 2 === 0')
    ->map('($i) => $i * $i')
    ->takeWhile('($i) => $i < 25')
    ->toArray();
// [4, 16]
```

### <a name="immutable-tuple"></a>Immutable\Tuple
- implements `Immutable\ITuple`
- basic Immutable Tuple
- must have at least 2 values (_otherwise it is just a single value_)
- is `eager` as possible
- allows `destructuring`, `matching` and `parsing`/`formatting`
```php
// parsing
Tuple::parse('(foo, bar)')->toArray();            // ['foo', 'bar']
Tuple::parse('("foo, bar", boo)')->toArray();     // ['foo, bar', 'boo']
Tuple::parse('(1, "foo, bar", true)')->toArray(); // [1, 'foo, bar', true]

// matching and comparing
Tuple::from([1, 1])->match('int', 'int');                      // true
Tuple::from([1, 2, 3])->isSame(Tuple::of(1, 2, 3));            // true
Tuple::of(10, 'Foo', null)->match('int', 'string', '?string'); // true

// parsing and matching
Tuple::parseMatch('(foo, bar)', 'string', 'string')->toArray();            // ['foo', 'bar']
Tuple::parseMatchTypes('(foo, bar)', ['string', 'string'])->toArray();     // ['foo', 'bar']

// invalid types
Tuple::parseMatch('(foo, bar, 1)', 'string', 'string');        // throws \InvalidArgumentException "Given tuple does NOT match expected types (string, string) - got (string, string, int)"

// formatting
Tuple::from([1, 'foo', null])->toString();        // '(1, "foo", null)'

// destructuring
$tuple  = Tuple::of('first', 2, 3);
$first  = $tuple->first();      // 'first'
$second = $tuple->second();     // 2
[$first, $second] = $tuple;     // $first = 'first'; $second = 2
[,, $third]       = $tuple;     // 3

// unpacking
sprintf('Title: %s | Value: %s', ...Tuple::of('foo', 'bar'))   // "Title: foo | Value: bar"
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
- To see more check: [MF/CallbackParser](https://github.com/MortalFlesh/CallbackParser)

### Usage:
```php
$map = new Mutable\Enhanced\Map();
$map->set(1, 'one');
$map[2] = 'two';

$map->toArray(); // [1 => 'one', 2 => 'two']

$map
    ->filter('($v, $k) => $k > 1')
    ->map('($v, $k) => $k . " - " . $v')
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
    ->filter('($v, $i) => $v->getId() > 1') // filter entities with id > 1
    ->map('($v, $i) => $v->getId()')        // map filtered entities to just ids
    ->reduce('($t, $v) => $t + $v');        // reduce ids to their sum

echo $sumOfIdsGreaterThan1;     // 5
```

### How does it work?
- it parses function from string and evaluate it with `eval()`


### Some performance tests:
- benchmarks and memory usage tests ([here](https://github.com/MortalFlesh/PerformanceTests))

## Lazy mapping
- if your `Collection` get mapped and filtered many times (_for readability_), it is not a problem
    - `map -> map -> filter -> map -> filter -> map` will iterate the collection only once (_for applying all modifiers at once_)
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
    - Tuple(key, value)
    - IList::first(callback|null): TValue|null
    - IMap::first(callback|null): Tuple|null
    - ICollection::create(iterable<TKey, mixed> $source, (mixed value, TKey index) => TValue): string
