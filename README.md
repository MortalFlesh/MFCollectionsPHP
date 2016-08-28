# MFCollections for PHP - WIP
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


## CollectionInterface
- basic interface for Collections
- extends `IteratorAggregate, Countable`


## ListInterface
- extends `CollectionInterface`

## MutableListInterface
- extend `ListInterface`
- adds methods for mutable Lists only


### ListCollection
- it's basic List Collection

### Enhanced\ListCollection
- extends `ListCollection`
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## MapInterface
- extends `CollectionInterface, ArrayAccess`

### Map
- it's basic Map Collection

### Enhanced\Map
- extends `Map`
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## Generic\CollectionInterface
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


## Immutable\ListInterface
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


## Immutable\MapInterface
- extends `CollectionInterface, ArrayAccess`

### Immutable\Map
- it's basic Immutable Map Collection

### Immutable\Enhanced\Map
- extends `Immutable\Map`
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## Installation:
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


## Arrow Functions

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

## Plans for next versions
- separation of callback parser (to be useable on its own)
- methods:
    - CollectionInterface::forAll(callback):bool
    - Map::firstBy(callback):mixed
    - MapInterface::firstKey()
    - MapInterface::firstValue() 
    - Touple(key, value)
    - Map::first():Touple
