<?php

namespace MF\Collection\Mutable;

interface IMap extends \MF\Collection\IMap, ICollection
{
    /** @return \MF\Collection\Immutable\IMap */
    public function asImmutable();
}
