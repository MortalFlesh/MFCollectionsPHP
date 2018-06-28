```php
interface ICollection extends \MF\Collection\ICollection
```

| Methods |
|---------|
| **ICollection<K, T>** |
| _public static function **of**(`array` $array): `BadMethodCallException`_ |
| public function **contains**(`T` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array<K, T>` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback, `string` ): `Generic\ICollection<K, M>` |
| public function **filter**(`callable<Filter>` $callback): `Generic\ICollection<K, T>` |
| public function **reduce**(`callable<Reducer>` $reducer, `T` $initialValue = _null_): `T` |

| Type | Callback |
|------|----------|
| **Each**     | (`T` $value, `K` $index): `void`  |
| **Mapper**   | (`T` $value, `K` $index): `M` |
| **Filter**   | (`T` $value, `K` $index): `bool`  |
| **Reducer**  | (`T` $total, `T` $value, `T` $index, `Generic\ICollection<K, T>` $collection): `T` |
