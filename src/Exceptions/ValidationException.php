<?php
/**
 * ValidationException
 */

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends \DomainException
{
    /** @var array<string, string> */
    public array $errors;

    public function __construct(string|array $message, int $code = 422, ?\Throwable $previous = null)
    {
        $this->errors = is_array($message) ? $message : ['general' => $message];
        parent::__construct(is_array($message) ? implode('; ', $message) : $message, $code, $previous);
    }
}
