<?php declare(strict_types=1);

namespace MF\Collection\Exception;

class TupleMatchException extends TupleException
{
    /**
     * @phpstan-param string[] $expectedTypes
     * @phpstan-param string[] $actualTypes
     */
    public static function forTypes(array $expectedTypes, array $actualTypes): self
    {
        return new static(
            sprintf(
                'Given tuple does NOT match expected types (%s) - got (%s).',
                implode(', ', $expectedTypes),
                implode(', ', $actualTypes),
            ),
        );
    }
}
