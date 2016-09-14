<?php

namespace MF\Collection\Mutable;

interface IList extends \MF\Collection\IList, ICollection
{
    /** @return mixed */
    public function shift();

    /** @return mixed */
    public function pop();

    /** @return \MF\Collection\Immutable\IList */
    public function asImmutable();
}
