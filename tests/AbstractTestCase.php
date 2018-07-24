<?php declare(strict_types=1);

namespace MF\Collection;

use Assert\Assertion;
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

    protected function ignore(...$args): void
    {
        // ignore anything
    }

    protected function forPHP(array $versionDifferences)
    {
        $version = sprintf('%s%s', PHP_MAJOR_VERSION, PHP_MINOR_VERSION);
        $this->assertArrayHasKey($version, $versionDifferences);

        return $versionDifferences[$version];
    }

    protected function findByKeyOrValue($needle): \Closure
    {
        return function ($key, $value) use ($needle) {
            return $key === $needle || $value === $needle;
        };
    }

    protected function findByValue($needle): \Closure
    {
        return function ($value) use ($needle) {
            return $value === $needle;
        };
    }
}
