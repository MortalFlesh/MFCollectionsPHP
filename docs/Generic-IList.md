```php
interface IList extends \MF\Collection\IList
```

| Methods |
|---------|
| **ICollection** |
| _public static function **of**(`array` $array): `BadMethodCallException`_ |
| public function **contains**(`T` $value): `bool` |
| public function **clear**(): `void` |
| public function **isEmpty**(): `bool` |
| public function **toArray**(): `array<T>` |
| public function **each**(`callable<Each>` $callback): `void` |
| public function **map**(`callable<Mapper>` $callback, `string` $mappedListValueType): `Generic\IList<M>` |
| public function **filter**(`callable<Filter>` $callback): `Generic\IList<T>` |
| public function **reduce**(`callable<Reducer>` $reducer, `R\|T` $initialValue = _null_): `R\|T` |
| **IList** |
| public function **add**(`T` $value): `void` |
| public function **unshift**(`T` $value): `void` |
| public function **first**(): `T` |
| public function **last**(): `T` |
| public function **sort**(): `Generic\IList<T>` |
| public function **removeFirst**(`T` $value): `void` |
| public function **removeAll**(`T` $value): `void` |
| **Generic\IList\<T\>** |
| public static function **ofT**(`string` $valueType, `array` $array): `Generic\IList<T>` |

| Type | Callback |
|------|----------|
| **Each**     | (`T` $value, `int` $index): `void`  |
| **Mapper**   | (`T` $value, `int` $index): `T` |
| **Filter**   | (`T` $value, `int` $index): `bool`  |
| **Reducer**  | (`R\|T` $total, `T` $value, `int` $index, `Generic\IList<T>` $collection): `R\|T` |
