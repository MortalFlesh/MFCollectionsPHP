```php
interface IMap extends \MF\Collection\IMap, ICollection
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `Immutable\IMap` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `Immutable\IMap` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback): `Immutable\IMap` |
| public function **filter**(`callable<Filter>` $callback): `Immutable\IMap` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |
| **IMap** |
| public function **containsKey**(`mixed` $key): `bool` |
| public function **find**(`mixed` $value): `mixed\|false` |
| public function **get**(`mixed` $key): `mixed` |
| public function **set**(`mixed` $key, `mixed` $value): `Immutable\IMap` |
| public function **remove**(`mixed` $key): `Immutable\IMap` |
| public function **keys**(): `Immutable\IList` |
| public function **values**(): `Immutable\IList` |
| **Immutable\IMap** |
| public function **asMutable**(): `Mutable\IMap` |

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `mixed` $index): `void`  |
| **Mapper**   | (`mixed` $key, `mixed` $value): `mixed` |
| **Filter**   | (`mixed` $key, `mixed` $value): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `mixed` $index, `Immutable\IMap` $collection): `mixed` |
