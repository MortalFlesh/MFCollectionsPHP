<?php declare(strict_types=1);

namespace MF\Collection\Immutable;

use MF\Collection\Assertion;
use MF\Collection\Exception\BadMethodCallException;
use MF\Collection\Exception\OutOfBoundsException;
use MF\Collection\Exception\OutOfRangeException;
use MF\Collection\Range;

class Seq implements ISeq
{
    public const INFINITE = Range::INFINITE;

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
    protected array $modifiers;

    private bool $isInfinite = false;

    /**
     * @param iterable|callable $iterable string is for arrow function; Callable must be () => iterable
     */
    public function __construct(iterable|callable $iterable)
    {
        $this->modifiers = [];
        $this->iterable = $iterable;
    }

    /**
     * Seq::of(1, 2, 3)
     * Seq::of(...$array, ...$array2)
     */
    public static function of(mixed ...$args): ISeq
    {
        return new static($args);
    }

    /**
     * F#: seq { for i in 1 .. 10 do yield i * i }
     * Seq::forDo(fn($i) => yield $i * $i, '1 .. 10')
     * 1, 4, 9, ... 100
     *
     * F#: seq { for i in 1 .. 10 -> i * i }
     * Seq::forDo(fn($i) => $i * $i, '1 .. 10')
     * 1, 4, 9, ... 100
     *
     * F#: seq { for i in 1 .. 10 do if $i % 2 === 0 then yield i }
     * Seq::forDo(fn($i) => if($i % 2 === 0) yield $i, '1 .. 10')
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
     * @param callable $callable (int) => mixed
     */
    public static function forDo(string|array $range, callable $callable): ISeq
    {
        [$start, $end, $step] = Range::parse($range);

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
     * @param callable $callable (State) => [State, State|null]
     * @param mixed $initialValue T: <State>
     * @return ISeq T: <State>
     */
    public static function unfold(callable $callable, mixed $initialValue): ISeq
    {
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
     */
    public static function createEmpty(): ISeq
    {
        return static::create([]);
    }

    /**
     * Alias for infinite range 1..Inf
     * Seq::range('1..Inf')
     */
    public static function infinite(): ISeq
    {
        return static::range([1, self::INFINITE]);
    }

    public static function from(array $array, bool $recursive = false): ISeq
    {
        if ($recursive) {
            throw new BadMethodCallException(sprintf('Method %s with recursive is not implemented.', __METHOD__));
        }

        return static::create($array);
    }

    /**
     * Seq::create([1,2,3], ($i) => $i * 2)
     * Seq::create(range(1, 10), ($i) => $i * 2)
     * Seq::create($list, ($i) => $i * 2)
     */
    public static function create(iterable $iterable, ?callable $callable = null): ISeq
    {
        if ($callable !== null) {
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
     */
    public static function range(string|array $range): ISeq
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
     * @param iterable|callable $iterable string is for arrow function; Callable must be () => iterable
     */
    public static function init(iterable|callable $iterable): ISeq
    {
        return new static($iterable);
    }

    public function getIterator(): iterable
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

    private function createOutOfRange(int $strictLimit, int $count): OutOfRangeException
    {
        return new OutOfRangeException(
            sprintf('Seq does not have %d items to take, it only has %d items.', $strictLimit, $count)
        );
    }

    private function mapValue(callable $modifier, mixed $value, mixed $key): mixed
    {
        $value = $modifier($value, $key);
        Assertion::notIsInstanceOf($value, \Generator::class, 'Mapping must not generate new values.');

        return $value;
    }

    /**
     * Seq takes exactly n items from sequence.
     * Note: If there is not enough items, it will throw an exception.
     *
     * @throws \OutOfRangeException
     */
    public function take(int $limit): ISeq
    {
        return $this->clone()
            ->addModifier(self::TAKE, $limit)
            ->setIsInfinite(false);
    }

    private function clone(): self
    {
        return clone ($this);
    }

    /**
     * Seq takes up to n items from sequence.
     * Note: If there is not enough items, it will return all items.
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
     * Seq::range('1..Inf')->takeWhile(fn($i) => $i < 100) creates [1, 2, 3, ..., 99]
     * Seq::infinite()->filter(fn($i) => $i % 2 === 0)->map(fn($i) => $i * $i)->takeWhile(fn($i) => $i < 25)->toArray(); creates [4, 16]
     *
     * @param callable $callable (Item, Key) => bool
     */
    public function takeWhile(callable $callable): ISeq
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
     * @param callable $reducer (total:mixed,value:mixed,index:mixed,collection:ISeq):mixed
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $this->assertFinite('reduce');

        $total = $initialValue;
        foreach ($this as $i => $value) {
            $total = $reducer($total, $value, $i, $this);
        }

        return $total;
    }

    private function assertFinite(string $intent): void
    {
        if ($this->isInfinite) {
            throw new OutOfBoundsException(sprintf('It is not possible to %s infinite seq.', $intent));
        }
    }

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     */
    public function filter(callable $callback): ISeq
    {
        return $this->clone()
            ->addModifier(self::FILTER, $callback)
            ->setIsInfinite(false);
    }

    private function addModifier(string $type, mixed $modifier): self
    {
        $this->modifiers[] = [$type, $modifier];

        return $this;
    }

    public function contains(mixed $value): bool
    {
        foreach ($this as $i) {
            if ($i === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable $callback (value:mixed,index:mixed):bool
     */
    public function containsBy(callable $callback): bool
    {
        foreach ($this as $i => $value) {
            if ($callback($value, $i) === true) {
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
    public function asMutable(): ICollection
    {
        throw new BadMethodCallException('Seq does not have mutable variant.');
    }

    public function count(): int
    {
        $this->assertFinite('count');
        if (!is_array($this->iterable) || !empty($this->modifiers)) {
            $this->iterable = $this->toArray();
        }

        return count($this->iterable);
    }

    /**
     * @param callable $callback (value:mixed,index:mixed):mixed
     */
    public function map(callable $callback): ISeq
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

    /** @param callable $callback (value:mixed,index:mixed):iterable */
    public function collect(callable $callback): ISeq
    {
        return $this
            ->map($callback)
            ->concat();
    }

    public function concat(): ISeq
    {
        return self::init(function (): iterable {
            foreach ($this as $i) {
                yield from $i;
            }
        });
    }

    public function implode(string $glue): string
    {
        return implode($glue, $this->toArray());
    }
}
