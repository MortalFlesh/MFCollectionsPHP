<?php declare(strict_types=1);

namespace MF\Collection\Helper;

use MF\Collection\AbstractTestCase;

class CallbackTest extends AbstractTestCase
{
    /** @dataProvider provideCallbacks */
    public function testShouldCurryCallbackAndExecuteItWithRightNumberOfArgs(
        callable $callback,
        array $args,
        mixed $expected,
    ): void {
        $result = Callback::curry($callback)(...$args);

        $this->assertSame($expected, $result);
    }

    public static function provideCallbacks(): array
    {
        return [
            // callback, args, expected
            'strlen' => [strlen(...), ['foo'], 3],
            'strlen with unused value' => [strlen(...), ['used', 'unused-value'], 4],
            'mb_strlen with encoding as string' => ['mb_strlen', ['used', null], 4],
            'mb_strlen with encoding' => [mb_strlen(...), ['used', null], 4],
            'mb_strlen with just a string' => [mb_strlen(...), ['used'], 4],
            'lambda' => [
                function ($one, $two, $three, $four) {
                    self::assertSame('one', $one);
                    self::assertSame('two', $two);
                    self::assertSame('three', $three);
                    self::assertSame('four', $four);

                    return 'done';
                },
                ['one', 'two', 'three', 'four'],
                'done',
            ],
            'lambda with optional args' => [
                function ($one, $two, $three = null, $four = null) {
                    self::assertSame('one', $one);
                    self::assertSame('two', $two);
                    self::assertSame('three', $three);
                    self::assertNull($four);

                    return 'done';
                },
                ['one', 'two', 'three'],
                'done',
            ],
            'staticCallback as array' => [
                [self::class, 'staticCallback'],
                ['13', 37],
                '1337',
            ],
            'staticCallback as string' => [
                self::class . '::staticCallback',
                ['13', 37],
                '1337',
            ],
            'staticCallback' => [
                self::staticCallback(...),
                ['13', 37],
                '1337',
            ],
            'invokable' => [
                new class(function (?string $one, ?string $two, ?string $three, ?string $four): void {
                    self::assertSame('one', $one);
                    self::assertSame('two', $two);
                    self::assertSame('three', $three);
                    self::assertNull($four);
                }) {
                    public function __construct(private \Closure $assert)
                    {
                    }

                    public function __invoke($one, $two, $three = null, $four = null)
                    {
                        call_user_func($this->assert, $one, $two, $three, $four);

                        return 'done';
                    }
                },
                ['one', 'two', 'three'],
                'done',
            ],
        ];
    }

    public static function staticCallback(string $one, int $two): string
    {
        return $one . $two;
    }

    public function testShouldCurryTheGivenCallback(): void
    {
        $data = ['1_', '2_', '3_', '4_'];

        $cb = Callback::curry(self::staticCallback(...));

        $result = [];
        foreach ($data as $i => $v) {
            $result[] = $cb($v, $i);
        }

        $this->assertSame(['1_0', '2_1', '3_2', '4_3'], $result);
    }
}
