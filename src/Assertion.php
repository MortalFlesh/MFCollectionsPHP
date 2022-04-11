<?php declare(strict_types=1);

namespace MF\Collection;

use Assert\Assertion as BaseAssertion;
use MF\Collection\Exception\InvalidArgumentException;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = InvalidArgumentException::class;
}
