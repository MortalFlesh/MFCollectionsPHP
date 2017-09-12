<?php

namespace MF\Tests;

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
}
