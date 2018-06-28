```php
interface IList extends \MF\Collection\IList, ICollection
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `Immutable\IList` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `Immutable\IList` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback): `Immutable\IList` |
| public function **filter**(`callable<Filter>` $callback): `Immutable\IList` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |
| **IList** |
| public function **add**(`mixed` $value): `Immutable\IList` |
| public function **unshift**(`mixed` $value): `Immutable\IList` |
| public function **first**(): `mixed` |
| public function **last**(): `mixed` |
| public function **sort**(): `Immutable\IList` |
| public function **removeFirst**(`mixed` $value): `Immutable\IList` |
| public function **removeAll**(`mixed` $value): `Immutable\IList` |
| **Immutable\IList** |
| public function **asMutable**(): `Mutable\IList`;

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `int` $index): `void`  |
| **Mapper**   | (`mixed` $value, `int` $index): `mixed` |
| **Filter**   | (`mixed` $value, `int` $index): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `int` $index, `Immutable\IList` $collection): `mixed` |
