<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use MF\Collection\Range;
use MF\Parser\CallbackParser;

class Seq implements \IteratorAggregate, ISeq
{
    public const INFINITE = 'Inf';

    private const TAKE = 'take';
    private const TAKE_UP_TO = 'take_up_to';
    private const TAKE_WHILE = 'take_while';

    /** @var iterable|callable Callable must be () => iterable */
    private $iterable;

    /**
     * Modifier =
     * | Map -> [mapper of callable]
     * | Filter -> [filter of callable]
     * | Take -> [limit of int]
     * | TakeUpTo -> [limit of int]
     * | TakeWhile -> [limit of callable]
     *
     * @var array array<Modifier>
     */
    protected $modifiers;

    /** @var bool */
    private $isInfinite = false;

    /** @var CallbackParser */
    private $callbackParser;

    /**
     * @param iterable|string|callable $iterable string is for arrow function; Callable must be () => iterable
     */
    public function __construct($iterable)
    {
        if ($iterable === null) {
            throw new \InvalidArgumentException('Iterable source for Seq must not be null.');
        }
        $this->modifiers = [];
        $this->callbackParser = new CallbackParser();

        $this->iterable = is_string($iterable)
            ? $this->callbackParser->parseArrowFunction($iterable)
            : $iterable;
    }

    /**
     * Seq::of(1, 2, 3)
     * Seq::of(...$array, ...$array2)
     *
     * @return ISeq
     */
    public static function of(...$args): ISeq
    {
        return new static($args);
    }

    /**
     * F#: seq { for i in 1 .. 10 do yield i * i }
     * Seq::forDo('($i) => yield $i * $i', '1 .. 10')
     * 1, 4, 9, ... 100
     *
     * F#: seq { for i in 1 .. 10 -> i * i }
     * Seq::forDo('($i) => $i * $i', '1 .. 10')
     * 1, 4, 9, ... 100
     *
     * F#: seq { for i in 1 .. 10 do if $i % 2 === 0 then yield i }
     * Seq::forDo('($i) => if($i % 2 === 0) yield $i', '1 .. 10')
     * 2, 4, 6, 8, 10
     *
     * F#:
     * let (height, width) = (10, 10)
     * seq { for row in 0 .. width - 1 do
     *      for col in 0 .. height - 1 do
     *          yield (row, col, row*width + col)
     * }
     * If you need more complex for loops for generating, use ISeq::init() instead
     *
     * @param string|array $range string is for range '1..10'
     * @param string|callable $callable (int) => mixed
     * @return ISeq
     */
    public static function forDo($range, $callable): ISeq
    {
        [$start, $end, $step] = Range::parse($range);
        $callable = (new CallbackParser())->parseArrowFunction($callable);

        if ($end === self::INFINITE) {
            $seq = new static(
                function () use ($start, $step, $callable) {
                    for ($i = $start; true; $i += $step) {
                        $item = $callable((int) $i);

                        if (is_iterable($item)) {
                            yield from $item;
                        } else {
                            yield $item;
                        }
                    }
                }
            );

            return $seq->setIsInfinite();
        }

        return new static(
            function () use ($callable, $step, $end, $start) {
                foreach (range($start, $end, $step) as $i) {
                    $item = $callable((int) $i);

                    if (is_iterable($item)) {
                        yield from $item;
                    } else {
                        yield $item;
                    }
                }
            }
        );
    }

    /**
     * @param string|callable $callable (State) => [State, State|null]
     * @param <State> $initialValue
     * @return ISeq<State>
     */
    public static function unfold($callable, $initialValue): ISeq
    {
        $callable = (new CallbackParser())->parseArrowFunction($callable);

        return new static(
            function () use ($callable, $initialValue) {
                $next = $initialValue;
                do {
                    [$total, $next] = $callable($next);
                    if (!$next) {
                        return;
                    }

                    yield $total;
                } while (true);
            }
        );
    }

    /**
     * Alias for empty sequence
     * Seq::create([])
     *
     * @return ISeq
     */
    public static function createEmpty(): ISeq
    {
        return static::create([]);
    }

    /**
     * Alias for infinite range 1..Inf
     * Seq::range('1..Inf')
     *
     * @return ISeq
     */
    public static function infinite(): ISeq
    {
        return static::range([1, self::INFINITE]);
    }

    public static function from(array $array, bool $recursive = false): ISeq
    {
        if ($recursive) {
            throw new \Exception(sprintf('Method %s with recursive is not implemented yet.', __METHOD__));
        }

        return static::create($array);
    }

    /**
     * Seq::create([1,2,3], ($i) => $i * 2)
     * Seq::create(range(1, 10), ($i) => $i * 2)
     * Seq::create($list, ($i) => $i * 2)
     *
     * @param string|callable|null $callable
     * @return ISeq
     */
    public static function create(iterable $iterable, $callable = null): ISeq
    {
        if ($callable !== null) {
            $callable = (new CallbackParser())->parseArrowFunction($callable);

            return new static(function () use ($iterable, $callable) {
                foreach ($iterable as $i) {
                    $item = $callable($i);

                    if (is_iterable($item)) {
                        yield from $item;
                    } else {
                        yield $item;
                    }
                }
            });
        }

        return new static($iterable);
    }

    /**
     * Seq::range([1, 10])
     * Seq::range([1, 10, 100])
     * Seq::range('1..10')
     * Seq::range('1..10..100')
     *
     * @param string|array $range
     * @return ISeq
     */
    public static function range($range): ISeq
    {
        [$start, $end, $step] = Range::parse($range);

        if ($end === self::INFINITE) {
            $seq = new static(
                function () use ($start, $step) {
                    for ($i = $start; true; $i += $step) {
                        yield $i;
                    }
                }
            );

            return $seq->setIsInfinite();
        }

        return new static(range($start, $end, $step));
    }

    private function setIsInfinite(bool $isInfinite = true): self
    {
        $this->isInfinite = $isInfinite;

        return $this;
    }

    /**
     * Seq::init(function() {
     *     while($line = readLines($file)){
     *         yield $line;
     *     }
     * })
     *
     * Seq::init(function() {
     *     while($database->hasBatch()){
     *         yield $database->fetchBatch();
     *     }
     * })
     *
     * @param iterable|string|callable $iterable string is for arrow function; Callable must be () => iterable
     * @return ISeq
     */
    public static function init($iterable): ISeq
    {
        return new static($iterable);
    }

    public function getIterator(): \Generator
    {
        $counter = 0;
        $strictLimit = null;

        // this `if` has to be here, because I do not know other way of iterating callable|iterable, or abstracting that
        if (is_callable($this->iterable)) {
            foreach (call_user_func($this->iterable) as $key => $value) {
                foreach ($this->modifiers as [$type, $modifier]) {
                    if ($type === self::MAP) {
                        $value = $this->mapValue($modifier, $value, $key);
                    } elseif ($type === self::FILTER && !$modifier($value, $key)) {
                        continue 2;
                    } elseif ($type === self::TAKE) {
                        $strictLimit = $modifier;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === self::TAKE_UP_TO) {
                        $strictLimit = null;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === self::TAKE_WHILE) {
                        $strictLimit = null;
                        if (!call_user_func($modifier, $value, $key)) {
                            break 2;
                        }
                    }
                }

                $counter++;
                yield $key => $value;
            }
        } else {
            foreach ($this->iterable as $key => $value) {
                foreach ($this->modifiers as [$type, $modifier]) {
                    if ($type === self::MAP) {
                        $value = $this->mapValue($modifier, $value, $key);
                    } elseif ($type === self::FILTER && !$modifier($value, $key)) {
                        continue 2;
                    } elseif ($type === self::TAKE) {
                        $strictLimit = $modifier;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === self::TAKE_UP_TO) {
                        $strictLimit = null;
                        if ($counter >= $modifier) {
                            break 2;
                        }
                    } elseif ($type === self::TAKE_WHILE) {
                        $strictLimit = null;
                        if (!call_user_func($modifier, $value, $key)) {
                            break 2;
                        }
                    }
                }

                $counter++;
                yield $key => $value;
            }
        }

        if ($strictLimit !== null && $counter < $strictLimit) {
            throw $this->createOutOfRange((int) $strictLimit, $counter);
        }

        $this->modifiers = [];
    }

    private function createOutOfRange(int $strictLimit, int $count): \OutOfRangeException
    {
        return new \OutOfRangeException(
            sprintf('Seq does not have %d items to take, it only has %d items.', $strictLimit, $count)
        );
    }

    private function mapValue(callable $modifier, $value, $key)
    {
        $value = $modifier($value, $key);
        if ($value instanceof \Generator) {
            throw new \InvalidArgumentException('Mapping must not generate new values.');
        }

        return $value;
    }

    /**
     * Seq takes exactly n items from sequence.
     * Note: If there is not enough items, it will throw an exception.
     *
     * @throws \OutOfRangeException
     * @return ISeq
     */
    public function take(int $limit): ISeq
    {
        return $this->clone()
            ->addModifier(self::TAKE, $limit)
            ->setIsInfinite(false);
    }

    private function clone(): self
    {
        return clone($this);
    }

    /**
     * Seq takes up to n items from sequence.
     * Note: If there is not enough items, it will return all items.
     *
     * @return ISeq
     */
    public function takeUpTo(int $limit): ISeq
    {
        return $this->clone()
            ->addModifier(self::TAKE_UP_TO, $limit)
            ->setIsInfinite(false);
    }

    /**
     * Seq takes items till callable will return false.
     * Note: Item and Key given to callable will be mapped by given mapping.
     *
     * @example
     * Seq::range('1..Inf')->takeWhile('($i) => $i < 100') creates [1, 2, 3, ..., 99]
     * Seq::infinite()->filter('($i) => $i % 2 === 0')->map('($i) => $i * $i')->takeWhile('($i) => $i < 25')->toArray(); creates [4, 16]
     *
     * @param string|callable $callable (Item, Key) => bool
     * @return ISeq
     */
    public function takeWhile($callable): ISeq
    {
        return $this->clone()
            ->addModifier(self::TAKE_WHILE, $callable)
            ->setIsInfinite(false);
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this as $i) {
            $array[] = $i;
        }

        return $array;
    }

    /**
     * @param string|callable $reducer (total:mixed,value:mixed,index:mixed,collection:ISeq):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $this->assertFinite('reduce');
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        $total = $initialValue;
        foreach ($this as $i => $value) {
            $total = $reducer($total, $value, $i, $this);
        }

        return $total;
    }

    private function assertFinite(string $intent): void
    {
        if ($this->isInfinite) {
            throw new \OutOfBoundsException(sprintf('It is not possible to %s infinite seq.', $intent));
        }
    }

    /**
     * @param string|callable $callback (value:mixed,index:mixed):bool
     * @return ISeq
     */
    public function filter($callback): ISeq
    {
        return $this->clone()
            ->addModifier(self::FILTER, $callback)
            ->setIsInfinite(false);
    }

    /** @param mixed $modifier */
    private function addModifier(string $type, $modifier): self
    {
        $this->modifiers[] = [
            $type,
            in_array($type, [self::MAP, self::FILTER, self::TAKE_WHILE], true)
                ? $this->callbackParser->parseArrowFunction($modifier)
                : $modifier,
        ];

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function contains($value): bool
    {
        foreach ($this as $i) {
            if ($i === $value) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        foreach ($this as $i) {
            return false;
        }

        return true;
    }

    public function clear(): ISeq
    {
        return static::createEmpty();
    }

    /** @deprecated Seq does not have a mutable variant */
    public function asMutable(): void
    {
        throw new \BadMethodCallException('Seq does not have mutable variant.');
    }

    public function count(): int
    {
        $this->assertFinite('count');
        if (is_callable($this->iterable) || !empty($this->modifiers)) {
            $this->iterable = $this->toArray();
        }

        return count($this->iterable);
    }

    /**
     * @param string|callable $callback (value:mixed,index:mixed):mixed
     * @return ISeq
     */
    public function map($callback): ISeq
    {
        return $this->clone()
            ->addModifier(self::MAP, $callback);
    }

    /** @param callable $callback (value:mixed,index:mixed):void */
    public function each(callable $callback): void
    {
        foreach ($this as $k => $i) {
            $callback($i, $k);
        }
    }
}
