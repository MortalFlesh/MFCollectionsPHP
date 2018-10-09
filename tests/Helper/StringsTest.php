<?php declare(strict_types=1);

namespace MF\Collection\Helper;

use MF\Collection\AbstractTestCase;

class StringsTest extends AbstractTestCase
{
    /** @dataProvider provideContains */
    public function testShouldContainsString(string $haystack, string $needle, bool $expected): void
    {
        $result = Strings::contains($haystack, $needle);

        $this->assertSame($expected, $result);
    }

    public function provideContains(): array
    {
        return [
            // haystack, needle, expected
            'empty' => ['', '', true],
            'empty needle' => ['foo', '', true],
            'fooBar - contains' => ['fooBar', 'oB', true],
            'fooBar -  not contains' => ['fooBar', 'ob', false],
        ];
    }

    /** @dataProvider provideStartsWith */
    public function testShouldStartsWith(string $haystack, string $prefix, bool $expected): void
    {
        $result = Strings::startsWith($haystack, $prefix);

        $this->assertSame($expected, $result);
    }

    public function provideStartsWith(): array
    {
        return [
            // haystack, prefix, expected
            'empty' => ['', '', true],
            'empty prefix' => ['foo', '', true],
            'fooBar - starts with' => ['fooBar', 'foo', true],
            'fooBar -  not starts with' => ['fooBar', 'ob', false],
        ];
    }

    /** @dataProvider provideEndsWith */
    public function testShouldEndsWith(string $haystack, string $suffix, bool $expected): void
    {
        $result = Strings::endsWith($haystack, $suffix);

        $this->assertSame($expected, $result);
    }

    public function provideEndsWith(): array
    {
        return [
            // haystack, prefix, expected
            'empty' => ['', '', true],
            'empty suffix' => ['foo', '', true],
            'fooBar - ends with' => ['fooBar', 'Bar', true],
            'fooBar -  not ends with' => ['fooBar', 'ob', false],
        ];
    }
}
