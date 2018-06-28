```php
interface ICollection extends \MF\Collection\ICollection
```

| Methods |
|---------|
| **ICollection** |
| public static function **of**(`array` $array): `Immutable\ICollection` |
| public function **contains**(`mixed` $value): `bool` |
| public function **clear**(): `Immutable\ICollection` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback): `Immutable\ICollection` |
| public function **filter**(`callable<Filter>` $callback): `Immutable\ICollection` |
| public function **reduce**(`callable<Reducer>` $reducer, `mixed` $initialValue = _null_): `mixed` |
| **Immutable\ICollection** |
| public function **asMutable**(): `Mutable\ICollection` |

| Type | Callback |
|------|----------|
| **Each**     | (`mixed` $value, `mixed` $index): `void`  |
| **Mapper**   | (`mixed` $value, `mixed` $index): `mixed` |
| **Filter**   | (`mixed` $value, `mixed` $index): `bool`  |
| **Reducer**  | (`mixed` $total, `mixed` $value, `mixed` $index, `Immutable\ICollection` $collection): `mixed` |
