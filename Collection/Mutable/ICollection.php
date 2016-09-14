<?php

namespace MF\Collection\Mutable;

interface ICollection extends \MF\Collection\ICollection
{
    /** @return \MF\Collection\Immutable\ICollection */
    public function asImmutable();
}
