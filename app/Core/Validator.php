<?php

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function __construct(private array $data, private array $rules)
    {
        $this->run();
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $messages) {
            return $messages[0];
        }

        return null;
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $this->applyRule($field, $value, $rule, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        $isEmpty = $value === null || $value === '';

        switch ($rule) {
            case 'required':
                if ($isEmpty) {
                    $this->addError($field, "The {$field} field is required.");
                }
                break;

            case 'email':
                if (!$isEmpty && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Please enter a valid email address.');
                }
                break;

            case 'phone':
                if (!$isEmpty && !preg_match('/^\+?[0-9]{7,15}$/', (string) $value)) {
                    $this->addError($field, 'Please enter a valid phone number.');
                }
                break;

            case 'min':
                if (!$isEmpty && is_string($value) && mb_strlen($value) < (int) $params[0]) {
                    $this->addError($field, "The {$field} must be at least {$params[0]} characters.");
                } elseif (!$isEmpty && is_numeric($value) && (float) $value < (float) $params[0]) {
                    $this->addError($field, "The {$field} must be at least {$params[0]}.");
                }
                break;

            case 'max':
                if (!$isEmpty && is_string($value) && mb_strlen($value) > (int) $params[0]) {
                    $this->addError($field, "The {$field} must not exceed {$params[0]} characters.");
                }
                break;

            case 'numeric':
                if (!$isEmpty && !is_numeric($value)) {
                    $this->addError($field, "The {$field} must be numeric.");
                }
                break;

            case 'integer':
                if (!$isEmpty && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "The {$field} must be an integer.");
                }
                break;

            case 'in':
                if (!$isEmpty && !in_array((string) $value, $params, true)) {
                    $this->addError($field, "The selected {$field} is invalid.");
                }
                break;

            case 'date':
                if (!$isEmpty && strtotime((string) $value) === false) {
                    $this->addError($field, "The {$field} must be a valid date.");
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!$isEmpty && ($this->data[$confirmField] ?? null) !== $value) {
                    $this->addError($field, "The {$field} confirmation does not match.");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
