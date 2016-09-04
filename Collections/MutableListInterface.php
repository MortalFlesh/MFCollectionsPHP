<?php

namespace MF\Collections;

interface MutableListInterface extends ListInterface
{
    /** @return mixed */
    public function shift();

    /** @return mixed */
    public function pop();

    /** @return \MF\Collections\Immutable\ListInterface */
    public function asImmutable();
}
