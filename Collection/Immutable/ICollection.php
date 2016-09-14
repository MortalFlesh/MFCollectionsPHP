<?php

namespace MF\Collection\Immutable;

interface ICollection extends \MF\Collection\ICollection
{
    /** @return static */
    public function clear();

    /** @return \MF\Collection\Mutable\ICollection */
    public function asMutable();
}
