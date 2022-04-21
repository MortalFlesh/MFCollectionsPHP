<?php declare(strict_types=1);

namespace MF\Collection\Helper;

use MF\Collection\Exception\InvalidArgumentException;

/** @internal */
class Callback
{
    /**
     * @phpstan-template TOne
     * @phpstan-template TTwo
     * @phpstan-template TThree
     * @phpstan-template TFour
     * @phpstan-template TReturn
     *
     * @phpstan-param callable(): TReturn|callable(TOne): TReturn|callable(TOne, TTwo): TReturn|callable(TOne, TTwo, TThree): TReturn|callable(TOne, TTwo, TThree, TFour): TReturn $callback
     * @phpstan-return callable(mixed ...$args): TReturn
     */
    public static function curry(callable $callback): callable
    {
        $ref = self::reflectionOf($callback);
        $all = $ref->getNumberOfParameters();
        $required = $ref->getNumberOfRequiredParameters();

        return fn (...$args) => $callback(...self::prepareArgs($all, $required, $args));
    }

    /** @see https://stackoverflow.com/questions/13071186/how-to-get-the-number-of-parameters-of-a-run-time-determined-callable */
    private static function reflectionOf(callable $callable): \ReflectionFunctionAbstract
    {
        if ($callable instanceof \Closure) {
            return new \ReflectionFunction($callable);
        }

        if (is_string($callable)) {
            $pcs = explode('::', $callable);

            return count($pcs) > 1
                ? new \ReflectionMethod($pcs[0], $pcs[1])
                : new \ReflectionFunction($callable);
        }

        if (!is_array($callable)) {
            $callable = [$callable, '__invoke'];
        }

        return new \ReflectionMethod($callable[0], $callable[1]);
    }

    /**
     * @phpstan-param mixed[] $args
     * @phpstan-return iterable<mixed>
     */
    private static function prepareArgs(int $allArgs, int $requiredArgs, array $args): iterable
    {
        if ($allArgs <= 0) {
            return [];
        }

        $given = count($args);

        if ($requiredArgs > $given) {
            throw new InvalidArgumentException(
                sprintf(
                    'Given callback needs %d args but only %d given.',
                    $requiredArgs,
                    $given,
                ),
            );
        }

        if ($given > $allArgs) {
            [$args] = array_chunk($args, $allArgs, false);
        }

        return $args;
    }
}
