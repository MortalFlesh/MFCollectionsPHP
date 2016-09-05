MFCollections for PHP - WIP
===========================

- Travis + Coveralls
[![Build Status](https://travis-ci.org/MortalFlesh/MFCollectionsPHP.svg?branch=master)](https://travis-ci.org/MortalFlesh/MFCollectionsPHP)
[![Coverage Status](https://coveralls.io/repos/github/MortalFlesh/MFCollectionsPHP/badge.svg?branch=master)](https://coveralls.io/github/MortalFlesh/MFCollectionsPHP?branch=master)

- Scrutinizer
[![Build Status](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/badges/build.png?b=master)](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MortalFlesh/MFCollectionsPHP/?branch=master)

It's basically a syntax sugar over classic array structure, which allows you to use it as classic array, but adds some cool features.


## Todo list for v1.0.0
|                    | List        | Map         |
|--------------------|-------------|-------------|
| Classic            | OK          | OK          |
| Enhanced           | OK          | OK          |
| Generic            | OK          | asImmutable |
| Immutable          | OK          | OK          |
| Immutable\Enhanced | OK          | OK          |
| Immutable\Generic  | OK          | X           |
| _____methods_____  | ___________ | ___________ |
| clear()            | X           | X           |
| isEmpty()          | X           | X           |
| allow Class::class | OK          | OK          |


## Table of Contents
- [Requirements](#requirements)
- [CollectionInterface](#collection-interface)
- [ListInterface](#list-interface)
    - [MutableListInterface](#mutable-list-interface)
- [MapInterface](#map-interface)
- [Generic\CollectionInterface](#generic-collection-interface)
- [Immutable\ListInterface](#immutable-list-interface)
- [Immutable\MapInterface](#immutable-map-interface)
- [Installation](#installation)
- [Arrow Functions](#arrow-functions)
- [Plans for next versions](#plans)


## <a name="requirements"></a>Requirements
- PHP 5.5
- `eval()` function for parsing [Arrow Functions](#arrow-functions)

## <a name="collection-interface"></a>CollectionInterface
- basic interface for Collections
- extends `IteratorAggregate, Countable`


## <a name="list-interface"></a>ListInterface
- extends `CollectionInterface`

### <a name="mutable-list-interface"></a>MutableListInterface
- extend `ListInterface`
- adds methods for mutable Lists only


### ListCollection
- it's basic List Collection

### Enhanced\ListCollection
- extends `ListCollection`
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## <a name="map-interface"></a>MapInterface
- extends `CollectionInterface, ArrayAccess`

### Map
- it's basic Map Collection

### Enhanced\Map
- extends `Map`
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## <a name="generic-collection-interface"></a>Generic\CollectionInterface
- extends `CollectionInterface`
- adds generic functionality to Collections, which will validate types

### Generic\ListCollection
- implements `Generic\CollectionInterface`
- extends `ListCollection`
- has defined value type and validates it
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods
```php
// list will accept only string values
$list = new Generic\ListCollection('string');
```

### Generic\Map
- implements `Generic\CollectionInterface`
- extends `Map`
- has defined key and value type and validates it
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods
```php
// map will accept only string keys and int values
$map = new Generic\Map('string', 'int');
```


## <a name="immutable-list-interface"></a>Immutable\ListInterface
- extends `CollectionInterface`

### Immutable\ListCollection
- it's basic Immutable List Collection

### Immutable\Enhanced\ListCollection
- extends `Immutable\ListCollection`
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods

### Immutable\Generic\ListCollection
- implements `Generic\CollectionInterface`
- extends `Immutable\ListCollection`
- has defined value type and validates it
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods
```php
// list will accept only string values
$list = new Immutable\Generic\ListCollection('string');
```


## <a name="immutable-map-interface"></a>Immutable\MapInterface
- extends `CollectionInterface, ArrayAccess`

### Immutable\Map
- it's basic Immutable Map Collection

### Immutable\Enhanced\Map
- extends `Immutable\Map`
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## <a name="installation"></a>Installation:
```
//composer.json
{
    "require": {
        "mf/collections-php": "^0.1.0"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/MortalFlesh/MFCollectionsPHP.git"
        }
    ]
}

// console
composer install
```


## <a name="arrow-functions"></a>Arrow Functions

### Usage:
```php
$map = new Enhanced\Map();
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

$list = new Generic\ListCollection(SimpleEntity::class);
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

## <a name="plans"></a>Plans for next versions
- methods:
    - CollectionInterface::forAll(callback):bool
    - Map::firstBy(callback):mixed
    - MapInterface::firstKey()
    - MapInterface::firstValue() 
    - Touple(key, value)
    - Map::first():Touple
