<?php
declare(strict_types=1);

namespace App\Application\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\Validatable;

class RequestValidator
{
    /**
     * Validate a payload against a set of field rules.
     *
     * @param array<string, mixed>      $data   The raw input (usually $request->getParsedBody())
     * @param array<string, Validatable> $rules  field name => Respect validator
     * @return array<string, mixed>             The validated data, with only the keys you defined rules for
     * @throws ValidationException              When any field fails validation
     */
    public function validate(array $data, array $rules): array
    {
        $errors = [];
        $clean = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;

            // Trim strings automatically — saves writing trim() everywhere
            if (is_string($value)) {
                $value = trim($value);
            }

            try {
                $rule->setName($field)->assert($value);
                $clean[$field] = $value;
            } catch (NestedValidationException $e) {
                // Multi-rule failure — take the first message for clarity
                $messages = $e->getMessages();
                $errors[$field] = reset($messages) ?: "Invalid {$field}";
            } catch (RespectValidationException $e) {
                $errors[$field] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $clean;
    }
}