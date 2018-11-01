<?php declare(strict_types=1);

namespace MF\Collection\Exception;

class TupleBadMethodCallException extends BadMethodCallException implements TupleExceptionInterface
{
    public static function forAlteringTuple(): self
    {
        return new static('Altering existing tuple is not permitted.');
    }
}
