<?php

namespace MF\Tests;

use Assert\Assertion;
use MF\Collection\ICollection;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    use \Eris\TestTrait;

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

    protected function pbtMessage(array $source, array $result, string $problemDescription = 'is wrong'): string
    {
        return sprintf(
            'This array [%s] %s. Result is [%s].',
            implode(', ', $source),
            $problemDescription,
            implode(', ', $result)
        );
    }

    protected function assertSameItems(ICollection $source, ICollection $result, string $notSameMessage): void
    {
        $containsSameItems = true;

        foreach ($source as $value) {
            $containsSameItems = $containsSameItems && $result->contains($value);
        }

        $this->assertTrue($containsSameItems, $notSameMessage);
    }

    protected function assertSorted(ICollection $expectedSorted, string $notSortedMessage): void
    {
        $isSorted = true;
        $expectedSortedValues = array_values($expectedSorted->toArray());

        foreach ($expectedSortedValues as $index => $value) {
            $nextIndex = $index + 1;
            $isLastItem = !array_key_exists($nextIndex, $expectedSortedValues);
            $isCurrentValueLowerThenNext = !$isLastItem && $value <= $expectedSortedValues[$nextIndex];

            $isSorted = $isSorted && ($isCurrentValueLowerThenNext || $isLastItem);
        }

        $this->assertTrue($isSorted, $notSortedMessage);
    }
}
