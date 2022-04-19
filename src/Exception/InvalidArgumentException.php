<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use Assert\AssertionFailedException;

class InvalidArgumentException extends \InvalidArgumentException implements CollectionExceptionInterface, AssertionFailedException
{
    public static function forFailedAssertion(AssertionFailedException $e): self
    {
        return new static(
            $e->getMessage(),
            (int) $e->getCode(),
            $e->getPropertyPath(),
            $e->getValue(),
            $e->getConstraints(),
            $e,
        );
    }

    /** @phpstan-param mixed[] $constraints */
    public function __construct(
        string $message,
        int $code = null,
        private ?string $propertyPath = null,
        private mixed $value = null,
        private array $constraints = [],
        \Throwable $previous = null,
    ) {
        parent::__construct($message, (int) $code, $previous);
    }

    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /** @phpstan-return mixed[] */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
