```php
interface ICollection extends \MF\Collection\ICollection
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `Mutable\ICollection` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callback<Mapper>` $callback): `Mutable\ICollection` |
| public function **filter**(`callback<Filter>` $callback): `Mutable\ICollection` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |
| **Mutable\ICollection** |
| public function **asImmutable**(): `Immutable\ICollection` |

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `mixed` $index): `void`  |
| **Mapper**   | (`mixed` $value, `mixed` $index): `mixed` |
| **Filter**   | (`mixed` $value, `mixed` $index): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `mixed` $index, `Mutable\ICollection` $collection): `mixed` |
