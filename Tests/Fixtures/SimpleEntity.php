<?php

namespace MFCollections\Tests\Fixtures;

class SimpleEntity implements EntityInterface
{
    /** @var int */
    private $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
