<?php

namespace MF\Collection\Mutable;

interface MapInterface extends \MF\Collection\MapInterface, CollectionInterface
{
    /** @return \MF\Collection\Immutable\MapInterface */
    public function asImmutable();
}
