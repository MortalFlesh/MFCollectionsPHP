```php
interface IMap extends ICollection, \ArrayAccess
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `IMap` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback): `IMap` |
| public function **filter**(`callable<Filter>` $callback): `IMap` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |
| **IMap** |
| public function **containsKey**(`mixed` $key): `bool` |
| public function **find**(`mixed` $value): `mixed\|false` |
| public function **get**(`mixed` $key): `mixed` |
| public function **set**(`mixed` $key, `mixed` $value): `void` |
| public function **remove**(`mixed` $key): `void` |
| public function **keys**(): `IList` |
| public function **values**(): `IList` |

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `mixed` $index): `void`  |
| **Mapper**   | (`mixed` $key, `mixed` $value): `mixed` |
| **Filter**   | (`mixed` $key, `mixed` $value): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `mixed` $index, `IMap` $collection): `mixed` |
