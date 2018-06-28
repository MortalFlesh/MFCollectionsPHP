# Changelog

<!-- There is always Unreleased section on the top. Subsections (Add, Changed, Fix, Removed) should be Add as needed. -->
## Unreleased
- [_dev only_] Drop `scrutinizer`
- [_dev only_] Update `coveralls` to new package
- [_dev only_] Remove `bin-dir` option from `composer.json`

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
