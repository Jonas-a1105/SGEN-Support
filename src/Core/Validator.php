<?php
namespace App\Core;

class Validator {
    private $errors = [];
    private $data = [];

    public function __construct(array $postData) {
        $this->data = $postData;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function fails(): bool {
        return !$this->passes();
    }

    public function check(string $field, string $rule, string $message = '', $param = null) {
        if (empty($this->errors[$field])) {
            $value = $this->data[$field] ?? null;

            switch ($rule) {
                case 'required':
                    if (empty($value)) {
                        $this->addError($field, $message ?: "El campo {$field} es obligatorio.");
                    }
                    break;
                case 'numeric':
                    if (!empty($value) && !is_numeric($value)) {
                        $this->addError($field, $message ?: "El campo {$field} debe ser numérico.");
                    }
                    break;
                case 'maxLength':
                    if (!empty($value) && strlen($value) > $param) {
                        $this->addError($field, $message ?: "El campo {$field} no debe superar los {$param} caracteres.");
                    }
                    break;
                case 'minLength':
                    if (!empty($value) && strlen($value) < $param) {
                        $this->addError($field, $message ?: "El campo {$field} debe tener al menos {$param} caracteres.");
                    }
                    break;
                case 'inList':
                    if (!empty($value) && is_array($param) && !in_array($value, $param)) {
                        $this->addError($field, $message ?: "El valor del campo {$field} no es válido.");
                    }
                    break;
                case 'email':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, $message ?: "El campo {$field} no es un email válido.");
                    }
                    break;
            }
        }
    }

    private function addError(string $field, string $message) {
        $this->errors[$field] = $message;
    }

    public function get(string $field) {
        return htmlspecialchars(trim($this->data[$field] ?? ''), ENT_QUOTES, 'UTF-8');
    }
    
    public function getEmail(string $field) {
        return filter_var($this->data[$field] ?? null, FILTER_SANITIZE_EMAIL);
    }

    public function getInt(string $field) {
        return filter_var($this->data[$field] ?? null, FILTER_SANITIZE_NUMBER_INT);
    }
}