<?php

namespace MFCollections\Tests\Services\Parsers;

use MFCollections\Services\Parsers\CallbackParser;

class CallbackParserTest extends \PHPUnit_Framework_TestCase
{
    /** @var CallbackParser */
    private $callbackParser;

    public function setUp()
    {
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param string $func
     *
     * @dataProvider invalidFuncProvider
     */
    public function testShouldThrowExceptionWhenArrayFuncIsNotRight($func)
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->callbackParser->parseArrowFunction($func);
    }

    public function invalidFuncProvider()
    {
        return [
            ['function' => 0],
            ['function' => '() => '],
            ['function' => '($k, $v, $i) =>'],
            ['function' => '(a, b) => a + b'],
            ['function' => '$a => 2'],
            ['function' => '$a => $a + 2;'],
            ['function' => '($a) -> $a + 2;'],
        ];
    }

    /**
     * @param string $function
     * @param array $args
     * @param mixed $expected
     *
     * @dataProvider functionProvider
     */
    public function testShouldParseArrayFunctionWithTwoParams($function, array $args, $expected)
    {
        $callback = $this->callbackParser->parseArrowFunction($function);

        $this->assertTrue(is_callable($callback));
        $this->assertEquals($expected, call_user_func_array($callback, $args));
    }

    public function functionProvider()
    {
        return [
            [
                'function' => '($k, $v) => $k . $v',
                'args' => ['key', 'value'],
                'expected' => 'keyvalue',
            ],
            [
                'function' => '($k, $v) => $k',
                'args' => ['key', 'value'],
                'expected' => 'key',
            ],
            [
                'function' => '($k, $v) => return $v * 2;',
                'args' => ['key', 2],
                'expected' => 4,
            ],
            [
                'function' => '($k, $v, $i) => ($k + $v) * $i;',
                'args' => [2, 3, 4],
                'expected' => 20,
            ],
            [
                'function' => '($k, $v) => $k > 2',
                'args' => [2, 'x'],
                'expected' => false,
            ],
            [
                'function' => '($k) => $k <= 2;',
                'args' => [2],
                'expected' => true,
            ],
            [
                'function' => '() => true',
                'args' => [],
                'expected' => true,
            ],
            [
                'function' => '() => {}',
                'args' => [],
                'expected' => null,
            ],
            [
                'function' => '($x) => {return $x;}',
                'args' => ['x'],
                'expected' => 'x',
            ],
            [
                'function' => '($x, $y) => {return $x;}',
                'args' => ['x', 'y'],
                'expected' => 'x',
            ],
        ];
    }

    public function testShouldReturnCallableCallbackRightAway()
    {
        $callable = function ($a) {
            return $a;
        };

        $callback = $this->callbackParser->parseArrowFunction($callable);

        $this->assertTrue(is_callable($callback));
        $this->assertEquals($callable, $callback);
    }
}
