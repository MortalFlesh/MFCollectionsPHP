# Changelog

<!-- There is always Unreleased section on the top. Subsections (Added, Changed, Fixed, Removed) should be added as needed. -->
## Unreleased

## 3.1.1 - 2018-01-02
- Fixed `composer.json` version

## 3.1.0 - 2018-01-02
- Added `implode` method to `IList`
- Added `create` method to `ICollection` (_it allows to create collection by callback_)

## 3.0.1 - 2017-12-20
- [_dev only_] Added `giorgiosironi/eris` for `Property Based Testing`
- [_dev only_] Added `AbstractTestCase` for all unit tests
- Fixed `sort` in `Generic/ListCollection` method, which did not return proper collection 

## 3.0.0 - 2017-10-14
- [**BC**] `of` renamed to `from`
- Added `of` (and `ofT`) to `IList`

## 2.1.0 - 2017-08-06
- `map` and `filter` methods are `lazy` (_they are applied together in **one loop** if possible_)

## 2.0.1 - 2017-08-06
- Fixed `reduce` in `Generic Collections` (_now it is able to reduce to another type - not only to `TValue`_)

## 2.0.0 - 2017-08-05
- PhpUnit updated
- code health dependencies added
- [**BC**] drop PHP 5 support, PHP 7.1 required
- `Generators` used for iterating collections

## 1.0.0 - 2016-09-14
### Added
- CHANGELOG.md file
