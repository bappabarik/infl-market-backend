<?php
declare(strict_types=1);

namespace App\Application\Validation;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /**
     * @param array<string, string> $errors  Field name => human-readable error
     */
    public function __construct(private array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message, 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}