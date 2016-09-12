<?php

namespace MF\Collection\Immutable;

interface CollectionInterface extends \MF\Collection\CollectionInterface
{
    /** @return \MF\Collection\Mutable\CollectionInterface */
    public function asMutable();
}
