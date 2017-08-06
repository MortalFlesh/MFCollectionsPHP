MFCollections for PHP
=====================

[![Latest Stable Version](https://img.shields.io/packagist/v/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![Total Downloads](https://img.shields.io/packagist/dt/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![License](https://img.shields.io/packagist/l/mf/collections-php.svg)](https://packagist.org/packages/mf/collections-php)
[![Build Status](https://travis-ci.org/MortalFlesh/MFCollectionsPHP.svg?branch=master)](https://travis-ci.org/MortalFlesh/MFCollectionsPHP)
[![Coverage Status](https://coveralls.io/repos/github/MortalFlesh/MFCollectionsPHP/badge.svg?branch=master)](https://coveralls.io/github/MortalFlesh/MFCollectionsPHP?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/?branch=master)

It's basically a syntax sugar over classic array structure, which allows you to use it as classic array, but adds some cool features.

## Table of Contents
- [Installation](#installation)
- [Requirements](#requirements)
- [Base Interfaces](#base-interfaces)
    - [ICollection](#collection-interface)
    - [IList](#list-interface)
    - [IMap](#map-interface)
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

### <a name="collection-interface"></a>ICollection
- basic Interface for Collections
- extends `IteratorAggregate, Countable`

### <a name="list-interface"></a>IList
- extends `ICollection`

### <a name="map-interface"></a>IMap
- extends `ICollection, ArrayAccess`


## Mutable Collections

### Interfaces
- `Mutable\ICollection`, `Mutable\IList`, `Mutable\IMap`
- extends base version of Interface
- adds methods for mutable Collections only

### Mutable\ListCollection
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

### Mutable\Map
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


## Immutable Collections
- `internal state` of Immutable\Collection instance will `never change`
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
- `Immutable\ICollection`, `Immutable\IList`, `Immutable\IMap`
- extends base version of Interface
- adds methods for immutable Collections only or alters method return value

### Immutable\ListCollection
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

### Immutable\Map
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


## Plans for next versions
- _even better_ documentation ([current](https://github.com/MortalFlesh/MFCollectionsPHP/wiki))
- **methods**:
    - ICollection::forAll(callback):bool
    - IMap::firstKey()
    - IMap::firstValue() 
    - Tuple(key, value)
    - IList::first(callback|null): TValue|null
    - IMap::first(callback|null): Tuple|null
