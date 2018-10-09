<?php declare(strict_types=1);

namespace MF\Collection\Helper;

class Strings
{
    public static function contains(string $haystack, string $needle): bool
    {
        return empty($needle)
            ? true
            : mb_strpos($haystack, $needle) !== false;
    }

    public static function startsWith(string $haystack, string $prefix): bool
    {
        return mb_substr($haystack, 0, mb_strlen($prefix)) === $prefix;
    }

    public static function endsWith(string $haystack, string $suffix): bool
    {
        return empty($suffix)
            ? true
            : mb_substr($haystack, -mb_strlen($suffix)) === $suffix;
    }
}
