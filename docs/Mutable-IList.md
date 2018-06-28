```php
interface IList extends \MF\Collection\IList, ICollection
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `Mutable\IList` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback): `Mutable\IList` |
| public function **filter**(`callable<Filter>` $callback): `Mutable\IList` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |
| **IList** |
| public function **add**(`mixed` $value): `void` |
| public function **unshift**(`mixed` $value): `void` |
| public function **first**(): `mixed` |
| public function **last**(): `mixed` |
| public function **sort**(): `Mutable\IList` |
| public function **removeFirst**(`mixed` $value): `void` |
| public function **removeAll**(`mixed` $value): `void` |
| **Mutable\IList** |
| public function **shift**(): `mixed` |
| public function **pop**(): `mixed` |
| public function **asImmutable**(): `Immutable\IList` |

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `int` $index): `void`  |
| **Mapper**   | (`mixed` $value, `int` $index): `mixed` |
| **Filter**   | (`mixed` $value, `int` $index): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `int` $index, `Mutable\IList` $collection): `mixed` |
