<?php declare(strict_types=1);

namespace MF\Collection;

use Assert\Assertion;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected const TIMER_MICROSECONDS = 1;
    protected const TIMER_MILISECONDS = 1 * 1000;
    protected const TIMER_SECONDS = 1 * 1000 * 1000;
    protected const TIMERS = [
        self::TIMER_MICROSECONDS,
        self::TIMER_MILISECONDS,
        self::TIMER_SECONDS,
    ];

    /** @var float */
    private $timer = 0;

    protected function startTimer(): void
    {
        $this->timer = microtime(true);
    }

    protected function stopTimer(int $timer = self::TIMER_MILISECONDS): float
    {
        $time = microtime(true) - $this->timer;
        Assertion::inArray($timer, self::TIMERS);

        return $time * $timer;
    }

    protected function ignore(mixed ...$args): void
    {
        // ignore anything
    }

    protected function forPHP(array $versionDifferences): mixed
    {
        $version = sprintf('%s%s', PHP_MAJOR_VERSION, PHP_MINOR_VERSION);
        $this->assertArrayHasKey($version, $versionDifferences);

        return $versionDifferences[$version];
    }

    protected function findByKeyOrValue(mixed $needle): \Closure
    {
        return function ($key, $value) use ($needle) {
            return $key === $needle || $value === $needle;
        };
    }

    protected function findByValue(mixed $needle): \Closure
    {
        return function ($value) use ($needle) {
            return $value === $needle;
        };
    }
}
