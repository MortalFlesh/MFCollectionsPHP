<?php declare(strict_types=1);

namespace MF\Collection\Fixtures;

class SimpleEntity implements EntityInterface
{
    public function __construct(private int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
