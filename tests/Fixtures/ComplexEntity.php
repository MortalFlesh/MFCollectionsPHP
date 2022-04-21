<?php declare(strict_types=1);

namespace MF\Collection\Fixtures;

class ComplexEntity implements EntityInterface
{
    public function __construct(private SimpleEntity $simpleEntity)
    {
    }

    public function getSimpleEntity(): SimpleEntity
    {
        return $this->simpleEntity;
    }
}
