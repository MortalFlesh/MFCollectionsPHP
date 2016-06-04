# MFCollections for PHP - WIP
It's basically a syntax sugar over classic array structure, which allows you to use it as classic array, but adds some cool features.

## CollectionInterface
- basic interface for Collections
- extends `IteratorAggregate, Countable`


## Generic CollectionInterface
- extends CollectionInterface
- adds generic functionality to Collections, which will validate types


## MapInterface
- implements: `CollectionInterface, ArrayAccess`
- can have associated keys

### Map
- it's basic Map Collection

### Enhanced Map
- extends Map
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods

### Generic Map
- implements `Generic\CollectionInterface`
- extends Map
- has defined key and value type and validates it
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## ListInterface
- implements: `CollectionInterface`
- has just values

### ListCollection
- it's basic List Collection

### Enhanced ListCollection
- extends ListCollection
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods

### Generic ListCollection
- implements `Generic\CollectionInterface`
- extends ListCollection
- has defined value type and validates it
- adds possibility of usage `Arrow Functions` in map(), filter() and reduce() methods


## Instalation:
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


## Usage:
```php
$map = new Enhanced\Map();
$map->set(1, 'one');
$map[2] = 'two';

$map->toArray(); // [1 => 'one', 2 => 'two']

$map
    ->filter('($k, $v) => $k > 1')
    ->map('($k, $v) => $k . " - " . $v')
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


## Arrow Functions - how does it work?
- it parses function from string and evaluate it with `eval()`
