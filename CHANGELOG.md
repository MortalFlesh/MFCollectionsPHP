# Changelog

<!-- There is always Unreleased section on the top. Subsections (Add, Changed, Fix, Removed) should be Add as needed. -->
## Unreleased

## 7.2.0 - 2023-11-28
- Add `Immutable/Generic/IList::partition` method

## 7.1.1 - 2023-11-25
- Fix return type of `sortBy` and `sortByDescending` methods

## 7.1.0 - 2023-05-19
- Add `Immutable\Generic\IList::choose` method
- Add `Immutable\Generic\ListCollection::choose` method

## 7.0.0 - 2022-04-21
- [**BC**] Require php 8.1
- [**BC**] Drop `recursive` option in `from` creators
- [**BC**] Drop non-generic variants of the collections
- Use phpstan generics only
  - Add phpstan annotations 
  - [**BC**] Remove explicit types in the generic collections
  - Drop dependency on `MF/TypeValidator`
- Use readonly properties in Immutable collections to ensure immutability
- Add `KVPair` class
- Add more methods
  - Immutable/Generic/ICollection
    - Add `forAll`
    - Add `implode`
  - Immutable/Generic/IList, Immutable/Generic/ISeq
    - Add `concat` (`concatList`, `concatSeq`)
    - Add `mapi`
    - Add `sort`
    - Add `sortDescending`
    - Add `sortBy`
    - Add `sortDescendingBy`
    - Add `unique`
    - Add `uniqueBy`
    - Add `reverse`
    - Add `sum`
    - Add `sumBy`
    - Add `min`
    - Add `minBy`
    - Add `max`
    - Add `maxBy`
    - Add `append`
    - Add `chunkBySize`
    - Add `splitInto`
    - Add `collect`
    - Add `countBy`
    - Add `groupBy`
    - Add `toSeq`
  - Immutable/Generic/ISeq (only)
    - Add `skip`
    - Add `skipWhile`
    - Add `toList`
  - Immutable/Generic/IMap, Mutable/Generic/IMap
    - Add `fromPairs`
    - Add `pairs`
    - Add `toList`
    - Add `totoSeq`
  - Immutable/ITuple
    - Add `fst` static methods
    - Add `snd` static methods
  - Mutable/Generic/IList
    - Add `concat`
    - Add `concatList`
    - Add `mapi`
    - Add `sort`
    - Add `sortDescending`
    - Add `sortBy`
    - Add `sortDescendingBy`
    - Add `unique`
    - Add `uniqueBy`
    - Add `reverse`
    - Add `sum`
    - Add `sumBy`
    - Add `min`
    - Add `minBy`
    - Add `max`
    - Add `maxBy`
    - Add `append`
    - Add `toSeq`
- Optimize callbacks and their execution with real number of arguments

## 6.1.1 - 2022-02-22
- Fix that applying modifiers removes all the current modifiers even, if there are no items or modifiers

## 6.1.0 - 2022-01-14
- Allow php 8.1 and update dependencies

## 6.0.0 - 2020-03-31
- [**BC**] Require php 8.0 and update dependencies

## 5.0.0 - 2020-03-31
- [**BC**] Require php 7.4 and update dependencies
- [**BC**] Remove Enhanced collections - _arrow functions are now in php itself_
- [**BC**] Remove Callback parser from everywhere
- [**BC**] Callbacks must be callable - not just any string

## 4.0.0 - 2019-12-02
- [**BC**] Drop support for PHP 7.1
- Update phpunit to 8
- Test leaks on PHP 7.3
- Supports PHP 7.3
- Fix `Assertion::isCallable` method signature

## 3.18.0 - 2018-11-02
- Explicitly require `ext-mbstring`
- Add `CollectionExceptionInterface` for all `AssertionFailedExceptions`
- Allow `Generators` in `arrow functions` (`yield`)
- **Not** use `Tuple` in _internal_ modifiers of `Map` and `ListCollection` anymore (_because of performance_)

## 3.17.0 - 2018-10-24
- Allow `callable` type in `PrioritizedCollection`
- Use `Tuple` in _internal_ modifiers of `Map` and `ListCollection`

## 3.16.1 - 2018-10-22
Fix `Tuple` constructor assertion for minimal items count. _It no more uses `var_export` because of `var_export` limitations._

## 3.16.0 - 2018-10-19
Fix `first` method of `IList` to return `null` if list is empty
Add `firstBy` method to `IList`

## 3.15.0 - 2018-10-17
- Allow `callable` type in `Generic` `Lists` and `Maps`

## 3.14.0 - 2018-10-10
- Expand `toStringForUrl` method not to quote string containing `_`, `-`, `.` and ` `

## 3.13.0 - 2018-10-10
- Add `toStringForUrl` method to `ITuple` to allow formatting for URL

## 3.12.0 - 2018-10-09
- Supports `array` in `Tuple`

## 3.11.0 - 2018-08-17
- Add `merge` and `mergeMatch` methods to `ITuple`
- Remove superfluous type annotations

## 3.10.0 - 2018-07-26
- Change return type of `getIterator` method to generic `iterable`
- Add `IEnumerable` interface to implement `IteratorAggregate` and `Countable`
- Add `PrioritizedCollection` to store values by priority

## 3.9.0 - 2018-07-24
- Add `containsBy` method to `ICollection`

## 3.8.0 - 2018-07-23
- Add `implode` method to `ISeq`
- Fix `callable` annotation for `arrow functions` to `callable|string`

## 3.7.0 - 2018-07-19
- Allow `mixed`/`any` type for `Generic Collections`

## 3.6.0 - 2018-07-17
- Fix missing an explicit requirement on `beberlei/assert` library
- Add `expectedItemsCount` _optional_ argument to `parse` method of `ITuple` to allow explicit validation of parsed result
- Require higher version (`^2.9.3`) of `beberlei/assert` library because of simplified error messages
- Allow `beberlei/assert` library in version `^3.0`
- Add `parseMatch` and `parseMatchTypes` to `ITuple` to validate parsed result
- Simplify `toString` method of `Tuple`

## 3.5.1 - 2018-07-13
- Fix `implode` on `ListCollection`
- Fix `count` on `Seq`

## 3.5.0 - 2018-06-28
- Allow `unpack` of `Tuples` by implementing `IteratorAggregate`

## 3.4.2 - 2018-06-28
- Fix `static creators` which are `deprecated` in some situation to allow `override` them

## 3.4.1 - 2018-06-28
- [_dev only_] Drop `scrutinizer`
- [_dev only_] Update `coveralls` to new package
- [_dev only_] Remove `bin-dir` option from `composer.json`
- [_dev only_] Add `matrix` to the `travis.yml` to optimize build time 
- [_dev only_] Change `phpunit.xml` namespace to local from `vendor` to match exact version
- Update `phpstan` and fix:
    - `Mutable\ListCollection` method `find` to strictly return `int|false`
    - `Immutable\ListCollection` method `find` to strictly return `int|false`
    - `Immutable\Seq` method `count` to correctly count an `iterable` source

## 3.4.0 - 2018-06-27
- Add `Tuple`

## 3.3.0 - 2018-06-24
- Add `collect` method to `Seq`
- Add `concat` method to `Seq`

## 3.2.2 - 2018-06-11
- Fix `Range` defined by string in `Seq`, which has a first value as `string` not as `numeric`

## 3.2.1 - 2018-05-29
- Add `annotations` to `Seq` from `ISeq`

## 3.2.0 - 2018-05-29
- Replace `code-sniffer` and `cs-fixer` for `lmc/coding-standards` for checking code style
- Update dev-dependencies
- Change `Collection` dir for `src`
- Change `Tests` dir for `tests`
- Change namespace for `Tests`
- Test different values for different php versions
- Add `ISeq` and `Seq` for creating sequences

## 3.1.1 - 2018-01-02
- Fix `composer.json` version

## 3.1.0 - 2018-01-02
- Add `implode` method to `IList`
- Add `create` method to `ICollection` (_it allows to create collection by callback_)

## 3.0.1 - 2017-12-20
- [_dev only_] Add `giorgiosironi/eris` for `Property Based Testing`
- [_dev only_] Add `AbstractTestCase` for all unit tests
- Fix `sort` in `Generic/ListCollection` method, which did not return proper collection 

## 3.0.0 - 2017-10-14
- [**BC**] Rename `of` to `from`
- Add `of` (and `ofT`) to `IList`

## 2.1.0 - 2017-08-06
- Make `map` and `filter` methods `lazy` (_they are applied together in **one loop** if possible_)

## 2.0.1 - 2017-08-06
- Fix `reduce` in `Generic Collections` (_now it is able to reduce to another type - not only to `TValue`_)

## 2.0.0 - 2017-08-05
- Update PhpUnit
- Add code health dependencies
- [**BC**] Drop PHP 5 support, PHP 7.1 required
- Use `Generators` for iterating collections

## 1.0.0 - 2016-09-14
- Add CHANGELOG.md file
