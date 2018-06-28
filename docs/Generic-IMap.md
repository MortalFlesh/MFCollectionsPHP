```php
interface IMap extends ICollection, \ArrayAccess
```

| Methods |
|---------|
| **ICollection** |
| _public static function **of**(`array` $array): `BadMethodCallException`_ |
| public function **contains**(`T` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array<K, T>` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback, `string` $mappedMapValueType): `Generic\IMap<K, M>` |
| public function **filter**(`callable<Filter>` $callback): `Generic\IMap<T>` |
| public function **reduce**(`callable<Reducer>` $reducer, `R\|T` $initialValue = _null_): `R\|T` |
| **IMap** |
| public function **containsKey**(`K` $key): `bool` |
| public function **find**(`T` $value): `K\|false` |
| public function **get**(`K` $key): `T` |
| public function **set**(`K` $key, `T` $value): `void` |
| public function **remove**(`K` $key): `void` |
| public function **keys**(): `Generic\IList<K>` |
| public function **values**(): `Generic\IList<T>` |
| **Generic\IMap<K, T>** |
| public static function **ofKT**(`string` $keyType, `string` $valueType, `array` $array): `Generic\IMap<K, T>`;

| Type | Callback |
|------|----------|
| **Each**     | (`T` $value, `K` $index): `void` |
| **Mapper**   | (`K` $key, `T` $value): `M` |
| **Filter**   | (`K` $key, `T` $value): `bool`  |
| **Reducer**  | (`R\|T` $total, `T` $value, `K` $index, `Generic\IMap<K, T>` $collection): `R\|T` |
