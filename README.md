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
- `PHP ^8.1`


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
- `Mutable\Generic\ICollection`, `Mutable\Generic\IList`, `Mutable\Generic\IMap`

### Mutable\Generic\ListCollection
- implements `Mutable\Generic\IList`
- is `eager` as possible

### Mutable\Generic\Map
- implements `Mutable\Generic\IMap`
- is `eager` as possible

### <a name="mutable-prioritized-collection"></a>Mutable\Generic\PrioritizedCollection
- implements `IEnumerable`
- holds items with `generic` type by `priority`
- is `eager` as possible

#### Example of strategies by priority
For case when you want to apply `only the first strategy` which can do what you want.
You can add strategies `dynamically` and still apply them `by priority` later. 
```php
// initialization of strategies
/** @phpstan-var PrioritizedCollection<StrategyInterface> $strategies */
$strategies = new PrioritizedCollection();
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
- `internal state` of Immutable\Collection instance will `never change` from the outside (it is `readonly`)
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
- `Immutable\Generic\ICollection`, `Immutable\Generic\IList`, `Immutable\Generic\IMap`, `Immutable\Generic\ISeq`, `Immutable\ITuple`

### <a name="immutable-list"></a>Immutable\Generic\ListCollection
- implements `Immutable\Generic\IList`
- is `eager` as possible

### <a name="immutable-map"></a>Immutable\Generic\Map
- implements `Immutable\Generic\IMap`
- is `eager` as possible

### <a name="immutable-seq"></a>Immutable\Seq
- implements `Immutable\Generic\ISeq`
- is `lazy` as possible (_even could be `Infinite`_)
```php
$seq = Seq::infinite()                         // 1, 2, ...
    ->filter(fn ($i) => $i % 2 === 0)   // 2, 4, ...
    ->skip(2)                           // 6, 8, ...
    ->map(fn ($i) => $i * $i)           // 36, 64, ...
    ->takeWhile(fn ($i) => $i < 100)    // 36, 64
    ->reverse()                         // 64, 36
    ->take(1);                          // 64
// for now the Sequence is still lazy

// this will generate (evaluate) the values
$array = $seq->toArray();               // [64]
```

### <a name="immutable-kvpair"></a>Immutable\Generic\KVPair
- always has a `Key` and the `Value`
- key is restricted to `int|string` so it may be used in the `foreach` as a key
- can contain any values

### <a name="immutable-tuple"></a>Immutable\Tuple
- implements `Immutable\ITuple`
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


## Sequences and lazy mapping
- if your `Sequence` get mapped and filtered many times (_for readability_), it is not a problem
    - `map -> map -> filter -> map -> filter -> map` will iterate the collection **only once** (_for applying all modifiers at once_)
    - this modification is done when another method is triggered, so adding new modifier is an **atomic** operation
- all the values are generated on the fly, so it may end on out of memory exception


## Plans for next versions
- use `Symfony/Stopwatch` in unit tests
- _even better_ documentation ([current](https://github.com/MortalFlesh/MFCollectionsPHP/wiki))
