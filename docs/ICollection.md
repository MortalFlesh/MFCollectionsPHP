```php
interface ICollection
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `ICollection` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback): `ICollection` |
| public function **filter**(`callable<Filter>` $callback): `ICollection` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `mixed` $index): `void`  |
| **Mapper**   | (`mixed` $value, `mixed` $index): `mixed` |
| **Filter**   | (`mixed` $value, `mixed` $index): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `mixed` $index, `ICollection` $collection): `mixed` |
