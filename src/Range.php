<?php declare(strict_types=1);

namespace MF\Collection;

class Range
{
    public const INFINITE = 'Inf';

    /** @param string|array $range */
    public static function parse($range): array
    {
        Assertion::true(
            is_string($range) || is_array($range),
            'Range can only be set by array or by string.'
        );

        if (is_string($range)) {
            $range = explode('..', str_replace(' ', '', $range));
        }

        $count = count($range);
        Assertion::between(
            $count,
            2,
            3,
            'Range must have [start, end] or [start, step, end] items. %s values given.'
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

    /**
     * @param float|int|string $start
     * @return float|int
     */
    private static function toNumeric($start)
    {
        return is_float($start)
            ? $start
            : (int) $start;
    }
}
