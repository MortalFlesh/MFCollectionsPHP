```php
interface IList extends ICollection
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `IList` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback): `IList` |
| public function **filter**(`callable<Filter>` $callback): `IList` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |
| **IList** |
| public function **add**(`mixed` $value): `void` |
| public function **unshift**(`mixed` $value): `void` |
| public function **first**(): `mixed` |
| public function **last**(): `mixed` |
| public function **sort**(): `IList` |
| public function **removeFirst**(`mixed` $value): `void` |
| public function **removeAll**(`mixed` $value): `void` |

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `int` $index): `void`  |
| **Mapper**   | (`mixed` $value, `int` $index): `mixed` |
| **Filter**   | (`mixed` $value, `int` $index): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `int` $index, `IList` $collection): `mixed` |
