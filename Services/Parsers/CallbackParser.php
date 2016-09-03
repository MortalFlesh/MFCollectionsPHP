<?php

namespace MFCollections\Services\Parsers;

class CallbackParser
{
    const FUNCTION_REGEX = '#^\(([A-z0-9, \$]*?){1}\)[ ]?\=\>[ ]?(.{1,})$#u';
    const PARAM_REGEX = '#^\$[A-z0-9\_]{1,}$#';
    const ARGUMENT_SEPARATOR = ',';
    const ARRAY_FUNCTION_OPERATOR = '=>';

    /**
     * @param string|callable $func
     * @return callable
     */
    public function parseArrowFunction($func)
    {
        if (is_callable($func)) {
            return $func;
        }

        $this->assertString($func);

        $func = trim($func);
        $this->assertSyntax($func);

        $parts = explode(self::ARRAY_FUNCTION_OPERATOR, $func, 2);  // ['($a, $b)', '$a + $b']
        $params = explode(self::ARGUMENT_SEPARATOR, str_replace(['(', ')', ' '], '', $parts[0]));   // ['$a', '$b']

        $this->assertParamsSyntax($params);

        $functionBody = trim(trim($parts[1], '; {}'), '; ');  // '$a + $b'

        if (strpos($functionBody, 'return') === false) {
            $functionString = sprintf('$callback = function(%s){return %s;};', implode(',', $params), $functionBody);
        } else {
            $functionString = sprintf('$callback = function(%s){%s;};', implode(',', $params), $functionBody);
        }
        eval($functionString);

        return $callback;
    }

    /**
     * @param string $string
     */
    private function assertString($string)
    {
        if (!is_string($string) || empty($string)) {
            throw new \InvalidArgumentException('Array function has to be string');
        }
    }

    /**
     * @param string $string
     */
    private function assertSyntax($string)
    {
        if (!preg_match(self::FUNCTION_REGEX, $string)) {
            throw new \InvalidArgumentException('Array function is not in right format');
        }
    }

    /**
     * @param array $params
     */
    private function assertParamsSyntax(array $params)
    {
        foreach ($params as $param) {
            if (!empty($param) && !preg_match(self::PARAM_REGEX, $param)) {
                throw new \InvalidArgumentException('Params are not in right format');
            }
        }
    }
}
