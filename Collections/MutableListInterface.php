<?php

namespace MFCollections\Collections;

interface MutableListInterface extends ListInterface
{
    /** @return mixed */
    public function shift();

    /** @return mixed */
    public function pop();

    /** @return \MFCollections\Collections\Immutable\ListInterface */
    public function asImmutable();
}
