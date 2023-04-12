<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Generic;

use MF\Collection\AbstractTestCase;
use MF\Collection\Immutable\ITuple;
use MF\Collection\Immutable\Tuple;

class KVPairTest extends AbstractTestCase
{
    /** @dataProvider provideTuple */
    public function testShouldCreateKVPairFromTuple(ITuple $tuple, KVPair $expected): void
    {
        $kvPair = KVPair::fromTuple($tuple);

        $this->assertEquals($expected, $kvPair);
    }

    public static function provideTuple(): array
    {
        return [
            // tuple, expected
            'minimum' => [Tuple::of('key', 'value'), new KVPair('key', 'value')],
            'more than 2' => [Tuple::of('key', 'value', 'other'), new KVPair('key', 'value')],
        ];
    }

    public function testShouldTransformToTuple(): void
    {
        $kvPair = new KVPair(1, 'one');

        $tuple = $kvPair->asTuple();

        $this->assertEquals(Tuple::of(1, 'one'), $tuple);
    }
}
