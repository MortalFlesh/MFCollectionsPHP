<?php declare(strict_types=1);

namespace MF\Collection\Exception;

use Assert\AssertionFailedException;

class InvalidArgumentException extends \InvalidArgumentException implements CollectionExceptionInterface, AssertionFailedException
{
    private array $constraints;

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

    public function __construct(
        string $message,
        int $code = null,
        private ?string $propertyPath = null,
        private mixed $value = null,
        array $constraints = null,
        \Throwable $previous = null,
    ) {
        parent::__construct($message, (int) $code, $previous);
        $this->constraints = $constraints ?? [];
    }

    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
