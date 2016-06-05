<?php

namespace MFCollections\Tests\Fixtures;

class ComplexEntity implements EntityInterface
{
    /** @var SimpleEntity */
    private $simpleEntity;

    /**
     * @param SimpleEntity $simpleEntity
     */
    public function __construct(SimpleEntity $simpleEntity)
    {
        $this->simpleEntity = $simpleEntity;
    }

    /**
     * @return SimpleEntity
     */
    public function getSimpleEntity()
    {
        return $this->simpleEntity;
    }
}
