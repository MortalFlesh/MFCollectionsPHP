<?php

namespace MF\Collection\Immutable;

interface CollectionInterface extends \MF\Collection\CollectionInterface
{
    /** @return static */
    public function clear();

    /** @return \MF\Collection\Mutable\CollectionInterface */
    public function asMutable();
}
