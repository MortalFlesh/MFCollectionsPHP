<?php

namespace MF\Collection\Mutable;

interface ListInterface extends \MF\Collection\ListInterface, CollectionInterface
{
    /** @return mixed */
    public function shift();

    /** @return mixed */
    public function pop();

    /** @return \MF\Collection\Immutable\ListInterface */
    public function asImmutable();
}
