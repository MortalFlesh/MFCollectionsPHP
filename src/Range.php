<?php declare(strict_types=1);

namespace MF\Collection;

class Range
{
    public static function parse($range): array
    {
        if (is_string($range)) {
            $range = explode('..', str_replace(' ', '', $range));
        }

        if (is_array($range)) {
            if (count($range) === 2) {
                [$start, $end] = $range;
                $step = 1;
            } elseif (count($range) === 3) {
                [$start, $step, $end] = $range;
            } else {
                throw new \InvalidArgumentException('Range must have [start, end] or [start, step, end] items.');
            }
        } else {
            throw new \InvalidArgumentException('Range can only be set by array or by string, see annotation.');
        }

        return [$start, $end, $step];
    }
}
