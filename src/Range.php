<?php declare(strict_types=1);

namespace MF\Collection;

class Range
{
    public const INFINITE = 'Inf';

    public static function parse(string|array $range): array
    {
        Assertion::true(
            is_string($range) || is_array($range),
            'Range can only be set by array or by string.',
        );

        if (is_string($range)) {
            $range = explode('..', str_replace(' ', '', $range));
        }

        $count = count($range);
        Assertion::between(
            $count,
            2,
            3,
            'Range must have [start, end] or [start, step, end] items. %s values given.',
        );

        if ($count === 2) {
            [$start, $end] = $range;
            $step = 1;
        } else {
            [$start, $step, $end] = $range;
        }

        return [
            self::toNumeric($start),
            $end === self::INFINITE ? $end : self::toNumeric($end),
            self::toNumeric($step),
        ];
    }

    private static function toNumeric(float|int|string $start): float|int
    {
        return is_float($start)
            ? $start
            : (int) $start;
    }
}
