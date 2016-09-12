<?php

namespace MF\Collection\Mutable;

interface CollectionInterface extends \MF\Collection\CollectionInterface
{
    /** @return \MF\Collection\Immutable\CollectionInterface */
    public function asImmutable();
}
